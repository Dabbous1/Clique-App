<?php

namespace App\Models;

use App\Models\OrderLineItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_id', 'product_handle', 'product_variant_id', 'product_title', 'description', 'product_tags', 'product_image', 'product_vendor', 'option_set_id', 'status', 'product_created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_products', 'product_id', 'collection_id', 'product_id', 'collection_id')->using(CollectionProduct::class)->withTimestamps();
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'local_product_id', 'id');
    }

    public function line_items()
    {
        return $this->hasMany(OrderLineItem::class, 'product_id', 'product_id');
    }
}
