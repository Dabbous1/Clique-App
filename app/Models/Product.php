<?php

namespace App\Models;

use App\Models\OrderLineItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_price',
        'status',
        'final_price_egp',
        'shopify_id',
        'user_id',
        'name',
        'code',
        'brand',
        'category',
        'sub_category',
        'qty',
        'unit_cost_eur',
        'unit_cost_usd',
        'unit_cost_egp',
        'cost_of_gram_usd',
        'unit_weight_gram',
        'unit_cost_with_weight_cost_usd',
        'unit_cost_with_weight_cost_egp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
