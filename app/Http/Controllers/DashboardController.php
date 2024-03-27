<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\PricingParameter;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $response = $user->api()->rest('get', '/admin/api/2023-04/webhooks.json', []);
        $pricingParameter = PricingParameter::first();
        $products = Product::all();
        return Inertia::render('Dashboard', compact(['response' , 'pricingParameter', 'products', 'user']));
    }
    public function submitPricing(Request $request)
    {
        $pricing = PricingParameter::first();
        $pricing->update($request->all());
        // return ('success', 'Pricing Updated Successfully');
    }
}
