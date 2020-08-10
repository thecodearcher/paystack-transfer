<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaystackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */


Route::middleware('throttle:60,1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/signin', [AuthController::class, 'signin']);
        Route::post('/signup', [AuthController::class, 'signup']);
        Route::post('/refresh', [AuthController::class, 'refreshToken']);
    });

    Route::middleware('auth.jwt')->prefix('transfers')->group(function () {
        Route::post('/', [PaystackController::class, 'requestFundTransfer']);
        Route::get('/', [PaystackController::class, 'getTransfers']);
        Route::get('/{transferId}', [PaystackController::class, 'getTransfer']);
    });
});
