<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers;

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

//Route::get('/', function () {
//    return view('welcome');
//});
Route::prefix('/')->group(function() {
    Route::get('/initialData', [Controllers\ApiController::class, 'index']);
    Route::get('/subscriptions', [Controllers\ApiController::class, 'subscriptions']);
    Route::get('/discounts', [Controllers\ApiController::class, 'discounts']);
});

