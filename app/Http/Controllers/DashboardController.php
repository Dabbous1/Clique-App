<?php

namespace App\Http\Controllers;

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
        if (!$user->synced) {
            PricingParameter::create([
                'cost_of_kg' => 25.56,
                'gross_margin' => 25,
                'bm_egp_markup' => 5,
                'user_id' => $user->id
            ]);
            $this->fetchProducts($user);
        }
        $response = $user->api()->rest('get', '/admin/api/2023-04/webhooks.json', []);
        // $pricingParameters = PricingParameter::where('user_id', $user->id)->first();
        $pricingParameter = PricingParameter::first();
        $filter = $request->all();
        return Inertia::render('Dashboard', compact(['response', 'user', 'pricingParameter', 'filter']));
    }
    public function productsList(Request $request)
    {
        $products = Product::with(['variants' => function ($query) {
            $query->where('position', 1);
        }])->when($request->get('q'), function ($q) use ($request) {
            $q->where('name', 'like', '%' . request()->get('q') . '%');
        })->when($request->get('sort'), function ($q) use ($request) {
            $q->orderBy(...explode(' ', $request->get('sort')));
        })->when($request->get('trace'), function ($q) use ($request) {
            if($request->trace == 'Active'){
                $q->where('status', 'active');
            }
            if($request->trace == 'Draft'){
                $q->where('status', 'draft');
            }
        })
        ->select('products.*', \DB::raw('(SELECT SUM(qty) FROM product_variants WHERE product_id = products.id) AS count'))
        ->select('products.*', \DB::raw('(SELECT COUNT(*) FROM product_variants WHERE product_id = products.id) AS variants_count'))
        ->cursorPaginate($request->page_count);
        return responseJson(true, 'products retrieved successfully!', $products);
    }

    public function submitPricing(Request $request)
    {
        $user = Auth::user();
        $pricing = PricingParameter::where('user_id', $user->id)->first();
        $pricing->update($request->all());
        $products = Product::where('user_id', $user->id)->get();
        $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=EUR&symbols=USD');
        $rates = $rates->json();
        $usdRate = $rates['rates']['USD'];
        $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=EUR&symbols=EGP');
        $rates = $rates->json();
        $eurRate = 1 / $usdRate;
        $egpRate = $eurRate * $rates['rates']['EGP'];
        foreach ($products as $product) {
            UpdateDatabaseJob::dispatch($user, $product, $usdRate, $egpRate);
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
        }
        return sendResponse(true, 'Synced.');
    }
}
