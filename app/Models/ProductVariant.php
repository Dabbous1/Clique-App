<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'weight',
        'original_price',
        'product_id',
        'final_price_egp',
        'shopify_id',
        'code',
        'position',
        'qty',
        'unit_cost_eur',
        'unit_cost_usd',
        'unit_cost_egp',
        'cost_of_gram_usd',
        'unit_weight_gram',
        'unit_cost_with_weight_cost_usd',
        'unit_cost_with_weight_cost_egp',
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
