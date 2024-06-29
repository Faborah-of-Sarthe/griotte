<?php

use Laravel\Fortify\RoutePath;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
$verificationLimiter = config('fortify.limiters.verification', '6,1');

// Override middleware signed to use relative URL
Route::get(RoutePath::for('verification.verify', '/email/verify/{id}/{hash}'), [VerifyEmailController::class, '__invoke'])
->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard'), 'signed:relative', 'throttle:'.$verificationLimiter])
->name('verification.verify');
