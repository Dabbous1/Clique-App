<?php

namespace App\Models;

use App\Models\OrderLineItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $with = [
        'variants'
    ];
    protected $fillable = [
        'status',
        'shopify_id',
        'user_id',
        'name',
        'brand',
        'category',
        'sub_category',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }
    protected static function boot(){
        parent::boot();
        static::deleting(function ($product) {
            $product->variants->each->delete();
        });
    }
}
