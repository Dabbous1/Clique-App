<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_name',
        'order_id',
        'email',
        'contact_email',
        'phone_no',
        'order_created_at',
        'customer_name',
        'total',
        'payment_status',
        'fulfillment_status',
        'customer_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
