<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Product;
use App\Jobs\SyncProductJob;
use Illuminate\Http\Request;
use App\Jobs\UpdateDatabaseJob;
use App\Models\PricingParameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Traits\ShopifyProductTrait;

class DashboardController extends Controller
{
    use ShopifyProductTrait;
    public function index(Request $request)
    {
        $user = Auth::user();
        Log::info('DashboardController@index is called.');
        // $user->api()->rest('POST', '/admin/api/2024-01/webhooks.json', [
        //     'webhook' => [
        //         'topic' => 'products/delete',
        //         'format' => 'json',
        //         'address' => 'https://phpstack-1296962-4714452.cloudwaysapps.com/webhook/products-update'
        //     ]
        // ]);

        $response = $user->api()->rest('get', '/admin/api/2024-01/webhooks.json', []);

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        // if (!$user->synced) {
        //     PricingParameter::updateOrCreate(
        //         [
        //             'user_id' => $user->id,
        //         ]
        //         ,
        //         [
        //             'cost_of_kg' => 25.56,
        //             'gross_margin' => 25,
        //             'bm_egp_markup' => 5,
        //         ]
        //     );
        $this->fetchProducts($user);
        //}
        // $response = $user->api()->rest('get', '/admin/api/2024-01/webhooks.json', []);

        $pricingParameter = PricingParameter::where('user_id', $user->id)->first();
        
        $filter = $request->all();
        
        return Inertia::render('Dashboard', compact(['response', 'user', 'pricingParameter', 'filter']));
    }
    public function productsList(Request $request)
    {
        $products = auth()->user()->products()->with([
            'variants' => function ($query) {
                $query->where('position', 1)->orWhereNotExists(function ($subQuery) {
                    $subQuery->from('product_variants as pv')
                        ->whereColumn('pv.product_id', 'product_variants.product_id')
                        ->where('pv.position', '<', \DB::raw('product_variants.position'));
                });
            }
        ])->when($request->get('q'), function ($q) use ($request) {
            $q->where('name', 'like', '%' . request()->get('q') . '%');
        })->when($request->get('sort'), function ($q) use ($request) {
            $q->orderBy(...explode(' ', $request->get('sort')));
        })->when($request->get('trace'), function ($q) use ($request) {
            if ($request->trace == 'Active') {
                $q->where('status', 'active');
            }
            if ($request->trace == 'Draft') {
                $q->where('status', 'draft');
            }
            if ($request->trace == 'Archived') {
                $q->where('status', 'archived');
            }
        })
            ->select('products.*', \DB::raw('(SELECT SUM(qty) FROM product_variants WHERE product_id = products.id) AS count'), \DB::raw('(SELECT COUNT(*) FROM product_variants WHERE product_id = products.id) AS variants_count'))
            ->cursorPaginate($request->page_count);
        return responseJson(true, 'products retrieved successfully!', $products);
    }

    public function submitPricing(Request $request)
    {
        $user = Auth::user();
        $pricingParameters = PricingParameter::where('user_id', $user->id)->first();
        $pricingParameters->update($request->all());
        $products = Product::where('user_id', $user->id)->get();
        $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=EUR&symbols=USD');
        $rates = $rates->json();
        $usdRate = $rates['rates']['USD'];
        $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=USD&symbols=EGP');
        $rates = $rates->json();
        $egpRate = $rates['rates']['EGP'];
        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $variant->unit_cost_eur = $variant->original_price;
                $variant->unit_cost_usd = $variant->original_price * $usdRate;
                $variant->unit_cost_egp = ($variant->unit_cost_usd * $egpRate) + $pricingParameters->bm_egp_markup;
                $variant->unit_cost_with_weight_cost_usd = $variant->unit_cost_usd + ($variant->cost_of_gram_usd * $variant->unit_weight_gram);
                $variant->unit_cost_with_weight_cost_egp = ($variant->unit_cost_with_weight_cost_usd * $egpRate) + $pricingParameters->bm_egp_markup;
                $variant->final_price_egp = round((($variant->unit_cost_with_weight_cost_egp * $pricingParameters->gross_margin) / 100) + $variant->unit_cost_with_weight_cost_egp, 3);
                $variant->save();
            }
        }
        return sendResponse(true, 'Updated.');
    }
    public function syncProducts()
    {
        $user = Auth::user();
        $products = Product::where('user_id', $user->id)->get();
        $products = $products->map(function ($product) {
            return [
                'id' => $product->shopify_id,
                'status' => $product->tag === null && $product->status != 'archived' ? 'active' : $product->status,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->shopify_id,
                        'price' => $variant->final_price_egp
                    ];
                })
            ];
        });
        foreach ($products as $product) {
            SyncProductJob::dispatch($product, $user);
            Product::where('user_id', $user->id)->where('shopify_id', $product['id'])->update(['status' => $product['status'], 'tag' => $product['status']]);
        }
        return sendResponse(true, 'Synced.');
    }
    public function syncLatestPrice()
    {
        $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=EUR&symbols=USD');
        $rates = $rates->json();
        $usdRate = $rates['rates']['USD'];
        $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=USD&symbols=EGP');
        $rates = $rates->json();
        $egpRate = $rates['rates']['EGP'];
        $pricing_parameters = PricingParameter::all();
        foreach ($pricing_parameters as $pricing_parameter) {
            $products = Product::where('user_id', $pricing_parameter->user_id)->get();
            foreach ($products as $product) {
                $user = User::where('id', $product->user_id)->first();
                UpdateDatabaseJob::dispatch($user, $product, $usdRate, $egpRate);
            }
        }
        return sendResponse(true, 'Synced.');
    }
}
