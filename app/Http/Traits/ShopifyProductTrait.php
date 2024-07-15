<?php

namespace App\Http\Traits;

use App\Jobs\SyncProductJob;
use Exception;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\Product;
use App\Models\Collection;
use App\Jobs\GetProductJob;
use App\Models\PricingParameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

trait ShopifyProductTrait
{
    public function storeWithDatabase(User $user, $product, $usdRate, $egpRate)
    {
        $pricingParameters = PricingParameter::where('user_id' , $user->id)->first();
        $dbProduct = null;
        $is_gift_card = false;
        foreach ($product['variants'] as $line_item) {
            if ($line_item['fulfillment_service'] == 'gift_card') {
                $is_gift_card = true;
            }
        }
        if (!$is_gift_card) {
            DB::beginTransaction();
            $dbProduct = Product::create([
                'shopify_id' => $product['id'],
                'status' => $product['status'],
                'user_id' => $user->id,
                'name' => $product['title'],
                'brand' => $product['vendor'],
                'category' => $product['product_type'],
            ]);
            foreach ($product['variants'] as $variant) {
                $dbVariant = $dbProduct->variants()->create([
                    'shopify_id' => $variant['id'],
                    'weight' => $variant['weight'],
                    'original_price' => $variant['price'],
                    'code' => $variant['sku'],
                    'position' => $variant['position'],
                    'qty' => $variant['inventory_quantity'],
                    'unit_cost_eur' => $variant['price'],
                    'unit_cost_usd' => $variant['price'] * $usdRate,
                    'unit_weight_gram' => $variant['grams'],
                    'cost_of_gram_usd' => $pricingParameters->cost_of_kg ? ($pricingParameters->cost_of_kg / 1000): 0,
                ]);
                $dbVariant->unit_cost_egp = ($dbVariant->unit_cost_usd * $egpRate) + $pricingParameters->bm_egp_markup;
                $dbVariant->unit_cost_with_weight_cost_usd = $dbVariant->unit_cost_usd + ($dbVariant->cost_of_gram_usd * $dbVariant->unit_weight_gram);
                $dbVariant->unit_cost_with_weight_cost_egp = ($dbVariant->unit_cost_with_weight_cost_usd * $egpRate) + $pricingParameters->bm_egp_markup;
                $dbVariant->final_price_egp = $pricingParameters->gross_margin ?  round((($dbVariant->unit_cost_with_weight_cost_egp * $pricingParameters->gross_margin) / 100) + $dbVariant->unit_cost_with_weight_cost_egp ,  3) : $dbVariant->unit_cost_with_weight_cost_egp ;
                $dbVariant->save();
                $user->synced = true;
                $user->save();
            }
            DB::commit();
        }
        return $dbProduct;
    }
    // public function syncWithDatabase(User $user, $product)
    // {
    //     $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=EUR&symbols=USD');
    //     $rates = $rates->json();
    //     $usdRate = $rates['rates']['USD'];
    //     $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=USD&symbols=EGP');
    //     $rates = $rates->json();
    //     $egpRate = $rates['rates']['EGP'];
    //     $pricingParameters = PricingParameter::where('user_id' , $user->id)->first();
    //     $dbProduct = null;
    //     $is_gift_card = false;
    //     foreach ($product->variants as $line_item) {
    //         if ($line_item->fulfillment_service == 'gift_card') {
    //             $is_gift_card = true;
    //         }
    //     }
    //     if (!$is_gift_card) {
    //         DB::beginTransaction();
    //         $dbProduct = Product::updateOrCreate([
    //             'shopify_id' => $product->id,
    //             'user_id' => $user->id,
    //         ], [
    //             'status' => $product->status,
    //             'name' => $product->title,
    //             'brand' => $product->vendor,
    //             'category' => $product->product_type,
    //         ]);
    //         foreach ($product->variants as $variant) {
    //             $dbVariant = $dbProduct->variants()->updateOrCreate([
    //                 'shopify_id' => $variant->id,
    //             ], [
    //                 'weight' => $variant->weight,
    //                 'code' => $variant->sku,
    //                 'position' => $variant->position,
    //                 'qty' => $variant->inventory_quantity,
    //                 'unit_weight_gram' => $variant->grams,
    //                 'cost_of_gram_usd' => ($pricingParameters->cost_of_kg / 1000),
    //             ]);
    //             $dbVariant->unit_cost_eur = $dbVariant->original_price;
    //             $dbVariant->unit_cost_usd = $dbVariant->original_price * $usdRate;
    //             $dbVariant->unit_cost_egp = ($dbVariant->unit_cost_usd * $egpRate) + $pricingParameters->bm_egp_markup;
    //             $dbVariant->unit_cost_with_weight_cost_usd = $dbVariant->unit_cost_usd + ($dbVariant->cost_of_gram_usd * $dbVariant->unit_weight_gram);
    //             $dbVariant->unit_cost_with_weight_cost_egp = ($dbVariant->unit_cost_with_weight_cost_usd * $egpRate) + $pricingParameters->bm_egp_markup;
    //             $dbVariant->final_price_egp = round(((($dbVariant->unit_cost_with_weight_cost_egp * $pricingParameters->gross_margin) / 100) + $dbVariant->unit_cost_with_weight_cost_egp) , 3);
    //             $dbVariant->save();
    //         }
    //         DB::commit();
    //     }
    //     return $dbProduct;
    // }

    // public function attachCollection(User $user, Product $product)
    // {

    //     if ($product->user->is_admin) {
    //         $query = <<<GRAPHQL
    //         {
    //             product(id: "gid://shopify/Product/{$product->product_id}") {
    //                 collections(first: 10) {
    //                     edges {
    //                         node {
    //                             id: legacyResourceId
    //                             title
    //                             handle
    //                             image {
    //                                 url
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         GRAPHQL;

    //         $response = $user->api()->graph($query);

    //         if (isset($response['body']['data']['product']['collections'])) {
    //             $collections = $response['body']['data']['product']['collections'];
    //             $collections = $collections->toArray();
    //             $collections = removeNodesAndEdges($collections);

    //             foreach ($collections as $collection) {
    //                 $dbCollection = Collection::updateOrCreate([
    //                     'collection_id' => $collection['id'],
    //                 ], [
    //                     'title' => $collection['title'],
    //                     'handle' => $collection['handle'],
    //                     'image' => isset($collection['image']['url']) ? $collection['image']['url'] : null,
    //                 ]);

    //                 $dbCollection->products()->syncWithoutDetaching($product);
    //             }
    //         }
    //     }
    // }

    public function syncWithShopify($product, User $user)
    {
        $product_id = $product['id'];
        $method = 'PUT';
        $url = '/admin/api/2024-01/products/' . $product_id . '.json';
        $product['variants'] = is_array($product['variants']) ? $product['variants'] : $product['variants']->toArray();
        try {
            $response = $user->api()->rest($method, $url, [
                'product' => $product
            ]);
            if ($response['errors']) {
                Log::error($response['body']);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
    // public function deleteFromShopify(Product $product)
    // {
    //     $user = $product->user;
    //     $method = 'DELETE';
    //     $url = '/admin/api/2024-01/products/' . $product->product_id . '.json';

    //     try {
    //         $response = $user->api()->rest($method, $url);
    //         if (!$response['errors']) {
    //             $product->variants()->delete();
    //             $product->images()->delete();
    //             $product->delete();
    //         }
    //     } catch (\Exception $e) {
    //         Log::error($e->getMessage());
    //     }
    // }
    public function fetchProducts($user)
    {
        
        Log::info("Product fetch Called ... " );
        $perPage = 250;
        $productCountResponse = $user->api()->rest('GET', '/admin/api/2024-01/products/count.json');
        if ($productCountResponse['errors']) {
            return Log::error("Product Count Error Body: " . $productCountResponse['body']);
        }
        $productCount = $productCountResponse['body']['count'];
        Log::info("Product Count is this : " . $productCount);
        $iterations = ceil($productCount / $perPage);
        $next = null;
        try {
            for ($i = 0; $i < $iterations; $i++) {
                $response = $user->api()->rest('GET', '/admin/api/2024-01/products.json', [
                    'limit' => $perPage,
                    'page_info' => $next
                ]);
                if ($response['errors']) {
                    Log::error("Get Products API Response Error Body: " . json_encode($response['body'], JSON_PRETTY_PRINT));
                }
                if (isset($response['body']['products'])) {
                    $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=EUR&symbols=USD');
                    $rates = $rates->json();
                    $usdRate = $rates['rates']['USD'];
                    $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=USD&symbols=EGP');
                    $rates = $rates->json();
                    $egpRate = $rates['rates']['EGP'];
                    foreach ($response['body']['products'] as $product) {
                        GetProductJob::dispatch($user, $product, $usdRate, $egpRate);
                    }
                }
                $link = $response['link'];
                if ($link) {
                    $next = $link->next;
                }
            }
            Log::info($productCount . ' Products are added to the database.');
        } catch (Exception $e) {
            logger()->info("Error in Fetching Products: " );
            logger()->info(json_encode($e->getMessage()));
        }
    }
    public function updateDatabase($product, $user, $usdRate, $egpRate)
    {
        $pricingParameters = PricingParameter::where('user_id' , $user->id)->first();
        foreach ($product->variants as $variant) {
            $variant->unit_cost_eur = $variant->original_price;
            $variant->unit_cost_usd = $variant->original_price * $usdRate;
            $variant->unit_cost_egp = ($variant->unit_cost_usd * $egpRate) + $pricingParameters->bm_egp_markup;
            $variant->unit_cost_with_weight_cost_usd = $variant->unit_cost_usd + ($variant->cost_of_gram_usd * $variant->unit_weight_gram);
            $variant->unit_cost_with_weight_cost_egp = ($variant->unit_cost_with_weight_cost_usd * $egpRate) + $pricingParameters->bm_egp_markup;
            $variant->final_price_egp = $pricingParameters->gross_margin ?  round((($variant->unit_cost_with_weight_cost_egp * $pricingParameters->gross_margin) / 100) + $variant->unit_cost_with_weight_cost_egp , 3) : $variant->unit_cost_with_weight_cost_egp ;
            $variant->save();
        }
        $product = [
            'id' => $product->shopify_id,
            'status' =>  $product->tag === null && $product->status != 'archived' ? 'active' : $product->status,
            'variants' => $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->shopify_id,
                    'price' => $variant->final_price_egp
                ];
            })->toArray()
        ];
        SyncProductJob::dispatch($product, $user);
        Product::where('user_id', $user->id)->where('shopify_id', $product['id'])->update(['status' => $product['status'], 'tag' => $product['status']]);
    }
    public function updateWithDatabase(User $user, $product)
    {
        $dbProduct = null;
        $is_gift_card = false;
        foreach ($product->variants as $line_item) {
            if ($line_item->fulfillment_service == 'gift_card') {
                $is_gift_card = true;
            }
        }
        if (!$is_gift_card) {
            DB::beginTransaction();
            $dbProduct = Product::updateOrCreate([
                'shopify_id' => $product->id,
                'user_id' => $user->id,
            ], [
                'status' => $product->status,
                'name' => $product->title,
                'brand' => $product->vendor,
                'category' => $product->product_type,
                'tag' => $product->status,
            ]);
            foreach ($product->variants as $variant) {
                $dbProduct->variants()->updateOrCreate([
                    'shopify_id' => $variant->id,
                ], [
                    'weight' => $variant->weight,
                    'code' => $variant->sku,
                    'position' => $variant->position,
                    'qty' => $variant->inventory_quantity,
                ]);
            }
            DB::commit();
        }
        return $dbProduct;
    }
}
