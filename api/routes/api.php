<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SectionController;
use App\Http\Middleware\CheckOwnership;

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
    // autocomplete route
    Route::get('/products/autocomplete', [ProductController::class, 'autocomplete']);
    // route to get the current store's sections and products flagged as "to buy"
    Route::get('/products', [ProductController::class, 'index']);

    Route::middleware(CheckOwnership::class)->group(function() {
        // update product
        Route::patch('/products/{product}', [ProductController::class, 'update']);
        // delete product
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        // attach product to section
        Route::put('/products/{product}/set-section/{section}', [ProductController::class, 'attachToSection']);

    });

    // STORES
    // change the current store for the current user
    Route::put('/stores/{store}/set-current', [StoreController::class, 'updateCurrentStore']);
    // get the current store for the current user
    Route::get('/stores/current', [StoreController::class, 'getCurrentStore']);
    // return all the stores belonging to the current user
    Route::get('/stores', [StoreController::class, 'index']);
    // Create a new store
    Route::post('/stores', [StoreController::class, 'store']);

    Route::middleware(CheckOwnership::class)->group(function() {

        // get all the data of a store
        Route::get('/stores/{store}', [StoreController::class, 'show']);
        // update a store
        Route::patch('/stores/{store}', [StoreController::class, 'update']);
        // delete a store
        // Route::delete('/stores/{store}', [StoreController::class, 'destroy']);
        // Change the order of the sections
        Route::put('/stores/{store}/sections/reorder', [StoreController::class, 'updateSectionsOrder']);

    });

    // SECTIONS
    // Get all sections of the current store
    Route::get('/sections', [SectionController::class, 'index']);

    // Create a new section
    Route::post('/sections', [SectionController::class, 'store']);

    Route::middleware(CheckOwnership::class)->group(function() {
        //udpdate a section
        Route::patch('/sections/{section}', [SectionController::class, 'update']);
    });

});




