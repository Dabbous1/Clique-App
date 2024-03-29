<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingParameter extends Model
{
    use HasFactory;
    protected $fillable = [
        'cost_of_kg',
        'gross_margin',
        'bm_egp_markup',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
