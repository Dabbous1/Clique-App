<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'collection_id', 'collection_title', 'collection_handle', 'collection_image', 'collection_created_at'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'collection_products', 'collection_id', 'product_id', 'collection_id', 'product_id')->using(CollectionProduct::class)->withTimestamps();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
