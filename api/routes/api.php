<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\CheckOwnership;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\RecipeController;

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
Route::middleware('auth:sanctum', 'verified')->group(function() {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/user', [UserController::class, 'update']);

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
        Route::delete('/stores/{store}', [StoreController::class, 'destroy']);
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
        // delete a section
        Route::delete('/sections/{section}', [SectionController::class, 'destroy']);
    });

    // RECIPES
    // Get all recipes of the current user
    Route::get('/recipes', [RecipeController::class, 'index']);

    // Create a new recipe
    Route::post('/recipes', [RecipeController::class, 'store']);

    // Get the number of recipes to make
    Route::get('/recipes/count', [RecipeController::class, 'count']);

    Route::middleware(CheckOwnership::class)->group(function() {
        // Get a recipe
        Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
        // update a recipe
        Route::patch('/recipes/{recipe}', [RecipeController::class, 'update']);

        // Attach a product to a recipe
        Route::post('/recipes/{recipe}/products', [RecipeController::class, 'attachProduct']);
    });


});

// Override the default route for password reset
Route::get('/reset-password/{token}', function (string $token) {
    return;
})->middleware('guest')->name('password.reset');


