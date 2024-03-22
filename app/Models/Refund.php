<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'local_order_id', 'refund_id', 'order_id', 'refund_note', 'refund_amount'
    ];
}
