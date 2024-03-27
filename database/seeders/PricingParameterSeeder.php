<?php

namespace Database\Seeders;

use App\Models\PricingParameter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PricingParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PricingParameter::create([
            'cost_of_kg' => 25.56,
            'gross_margin' => 25.00,
            'bm_egp_markup' => 5.00,
        ]);
    }
}
