<?php

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\PricingParameter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Shopify\Clients\Graphql;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/sync-latest-price', [DashboardController::class, 'syncLatestPrice'])->name('sync-latest-price');
Route::group(['middleware' => ['verify.embedded', 'verify.shopify']], function () {
    // Route::get('/', function () {
    //     //testing
    // $shop = Auth::user();
    // $request = $shop->api()->rest('GET', '/admin/api/2023-04/products.json' , ['query' => "status=draft"]);
    //     // $price = $request['body']['container']['products'][0]['variants'][0]['price'];
    // dd($request['body']['container']['products'][0]);
    //     // $productId = $request['body']['container']['products'][0]['id'];
    //     // $request = $shop->api()->rest('PUT', '/admin/api/2023-04/products/'.$productId.'.json' , ['json' => ['product' => ['status' => 'active' ]]]);
    //     // dd($request);
    //     // $rates = Http::get('http://data.fixer.io/api/latest?access_key=42e27abfba793b7bd010a85b484d8dce&base=EUR&symbols=EGP');
    //     // $rates = $rates->json();
    //     // dd($rates['rates']['EGP']*$price);
    //     //testing

    //     // $user = Auth::user();
    //     // $response = $user->api()->rest('get', '/admin/api/2023-04/webhooks.json', []);

    //     return Inertia::render('Dashboard', compact('response'));
    // })->name('home');

    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::post('submit-pricing', [DashboardController::class, 'submitPricing'])->name('submit-pricing');
    Route::get('sync-produccts', [DashboardController::class, 'syncProducts'])->name('sync-produccts');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/fetch-products-list', [DashboardController::class, 'productsList'])->name('products.list');


    Route::delete('/table', [ProfileController::class, 'table'])->name('ic_logs.list');

});

require __DIR__ . '/auth.php';

