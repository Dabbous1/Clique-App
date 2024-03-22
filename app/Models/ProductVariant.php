<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'local_product_id', 'product_id', 'variant_id', 'title', 'price', 'option1', 'option2', 'option3', 'quantity'
    ];

    public function product(){
        return $this->belongsTo(Product::class,"local_product_id","id");
    }
}
