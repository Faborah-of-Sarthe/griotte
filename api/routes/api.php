<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SectionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



// Routes protected by Sanctum
Route::middleware('auth:sanctum')->group(function() {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // PRODUCTS
    // new product
    Route::post('/products', [ProductController::class, 'store']);
    // update product
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    // delete product
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    // autocomplete route
    Route::get('/products/autocomplete', [ProductController::class, 'autocomplete']);
    // attach product to section
    Route::put('/products/{product}/set-section/{section}', [ProductController::class, 'attachToSection']);
    // route to get the current store's sections and products flagged as "to buy"
    Route::get('/products', [ProductController::class, 'index']);

    // STORES
    // change the current store for the current user
    Route::put('/stores/{store}', [StoreController::class, 'update']);
    // change the current store for the current user
    Route::put('/stores/{store}/set-current', [StoreController::class, 'updateCurrentStore']);
    // get the current store for the current user
    Route::get('/stores/current', [StoreController::class, 'getCurrentStore']);
    // return all the stores belonging to the current user
    Route::get('/stores', [StoreController::class, 'index']);
    // get all the data of a store
    Route::get('/stores/{store}', [StoreController::class, 'show']);

    // SECTIONS
    // Get all sections of the current store
    Route::get('/sections', [SectionController::class, 'index']);

});




