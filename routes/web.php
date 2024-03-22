<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::group(['middleware' => ['verify.embedded', 'verify.shopify']], function () {
    Route::get('/', function () {

        $user = Auth::user();
        $response = $user->api()->rest('get', '/admin/api/2023-04/webhooks.json', []);

        return Inertia::render('EmbeddedApp', compact('response'));
    })->name('home');

    //EMBEDDED LINKS
});

Route::group(['middleware' => ['auth', 'verified']], function () {

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::delete('/table', [ProfileController::class, 'table'])->name('ic_logs.list');

    //NON EMBEDDED LINKS
});

require __DIR__ . '/auth.php';
