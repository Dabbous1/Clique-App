<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'user_id' => '2',
            'name' => 'Product 1',
            'code' => '1001',
            'brand' => 'Product Brand',
            'category' => 'Product Category',
            'sub_category' => 'Product Sub Category',
            'qty' => '10',
            'unit_cost_eur' => '10',
            'unit_cost_usd' => '10.85',
            'unit_cost_egp' => '519.34',
            'cost_of_gram_usd' => '0.025',
            'unit_weight_gram' => '180',
            'unit_cost_with_weight_cost_usd' => '4.5',
            'unit_cost_with_weight_cost_egp' => '218.33',
            'final_price_egp' => '1069.62',
            'status' => 'draft',
            'shopify_id' => '10000001',
            'original_price' => '10'
        ]);
    }
}
