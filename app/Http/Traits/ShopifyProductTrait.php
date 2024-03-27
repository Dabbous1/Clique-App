<?php

namespace App\Http\Traits;

use App\Models\User;
use GuzzleHttp\Client;
use App\Models\Product;
use App\Models\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;

trait ShopifyProductTrait
{
    public function syncWithDatabase(User $user, $product)
    {
        $dbProduct = null;
        $is_gift_card = false;

        foreach ($product['variants'] as $line_item) {
            if ($line_item['fulfillment_service'] == 'gift_card') {
                $is_gift_card = true;
            }
        }

        if (!$is_gift_card) {

            DB::beginTransaction();

            $dbProduct = Product::updateOrCreate([
                'handle' => $product['handle'],
                'user_id' => $user->id,
            ], [
                'product_id' => $product['id'],
                'body_html' => $product['body_html'],
                'vendor' => $product['vendor'],
                'product_type' => $product['product_type'],
                'title' => $product['title'],
                'published_at' => $product['published_at'],
                'template_suffix' => $product['template_suffix'],
                'status' => $product['status'],
                'published_scope' => $product['published_scope'],
                'tags' => $product['tags'],
                'image_id' => @$product['image']['id'],
                'created_at' => $product['created_at'],
                'updated_at' => $product['updated_at'],
            ]);

            $dbProduct->images()->delete();

            foreach ($product['images'] as $image) {
                $url = parse_url($image['src']);
                $image['src'] = $url['scheme'] . '://' . $url['host'] . $url['path'];

                $dbProduct->images()->create([
                    'src' => $image['src'],
                    'image_id' => $image['id'],
                    'created_at' => $image['created_at'],
                    'updated_at' => $image['updated_at'],
                    'position' => $image['position'],
                    'width' => $image['width'],
                    'height' => $image['height'],
                    'variant_ids' => json_encode($image['variant_ids']),
                    'alt' => $image['alt'],
                ]);
            }

            foreach ($product['variants'] as $variant) {
                $dbProduct->variants()->updateOrCreate([
                    'title' => $variant['title'],
                ], [
                    'variant_id' => $variant['id'],
                    'created_at' => $variant['created_at'],
                    'updated_at' => $variant['updated_at'],
                    'price' => $variant['price'],
                    'sku' => $variant['sku'],
                    'position' => $variant['position'],
                    'inventory_policy' => $variant['inventory_policy'],
                    'compare_at_price' => $variant['compare_at_price'],
                    'fulfillment_service' => $variant['fulfillment_service'],
                    'inventory_management' => $variant['inventory_management'],
                    'option1' => $variant['option1'],
                    'option2' => $variant['option2'],
                    'option3' => $variant['option3'],
                    'taxable' => $variant['taxable'],
                    'barcode' => $variant['barcode'],
                    'grams' => $variant['grams'],
                    'image_id' => $variant['image_id'],
                    'weight' => $variant['weight'],
                    'weight_unit' => $variant['weight_unit'],
                    'inventory_item_id' => $variant['inventory_item_id'],
                    'inventory_quantity' => $variant['inventory_quantity'],
                    'old_inventory_quantity' => $variant['old_inventory_quantity'],
                    'requires_shipping' => $variant['requires_shipping'],
                ]);
            }

            DB::commit();
        }

        // if (isset($dbProduct->product_id)) {
        //     $this->attachCollection($user, $dbProduct);
        // }

        return $dbProduct;
    }

    public function attachCollection(User $user, Product $product)
    {

        if ($product->user->is_admin) {
            $query = <<<GRAPHQL
            {
                product(id: "gid://shopify/Product/{$product->product_id}") {
                    collections(first: 10) {
                        edges {
                            node {
                                id: legacyResourceId
                                title
                                handle
                                image {
                                    url
                                }
                            }
                        }
                    }
                }
            }
            GRAPHQL;

            $response = $user->api()->graph($query);

            if (isset($response['body']['data']['product']['collections'])) {
                $collections = $response['body']['data']['product']['collections'];
                $collections = $collections->toArray();
                $collections = removeNodesAndEdges($collections);

                foreach ($collections as $collection) {
                    $dbCollection = Collection::updateOrCreate([
                        'collection_id' => $collection['id'],
                    ], [
                        'title' => $collection['title'],
                        'handle' => $collection['handle'],
                        'image' => isset($collection['image']['url']) ? $collection['image']['url'] : null,
                    ]);

                    $dbCollection->products()->syncWithoutDetaching($product);
                }
            }
        }
    }

    public function syncWithShopify(User $user, Product $product, $updateDatabase = true)
    {
        $product_id = null;
        $method = 'POST';
        $url = '/admin/api/2023-10/products.json';
        $productData = [];

        if ($product->product_id) {
            $response = $user->api()->rest('GET', '/admin/api/2023-10/products/' . $product->product_id . '.json');
            if (isset($response['body']['product'])) {
                $product_id = $response['body']['product']['id'];
                $method = 'PUT';
                $url = '/admin/api/2023-10/products/' . $product_id . '.json';
            }
        }

        if ($method === 'POST') {
            $productData = $product->load([
                'images' => function ($q) {
                    $q->selectRaw('product_id, src, position');
                },
                'variants' => function ($q) {
                    $q->selectRaw('*, variant_id as id, product_id');
                }
            ])->toArray();
        } else {
            $productData = $product->load([
                'images' => function ($q) {
                    $q->selectRaw('image_id as id, product_id, src, position');
                },
                'variants' => function ($q) {
                    $q->selectRaw('*, variant_id as id, product_id');
                }
            ])->toArray();
        }

        try {
            $response = $user->api()->rest($method, $url, [
                'product' => $productData
            ]);

            if (!$response['errors'] && isset($response['body']['product'])) {
                if ($updateDatabase) {
                    $this->syncWithDatabase($user, $response['body']['product']);
                }
            } else {
                Log::error($response['body']);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
    public function deleteFromShopify(Product $product)
    {
        $user = $product->user;
        $method = 'DELETE';
        $url = '/admin/api/2023-10/products/' . $product->product_id . '.json';

        try {
            $response = $user->api()->rest($method, $url);
            if (!$response['errors']) {
                $product->variants()->delete();
                $product->images()->delete();
                $product->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
