<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CartController;

Route::group(['middleware' => ['api']], function(){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::prefix('orders')->group(function(){
            Route::GET('/', [OrderController::class, 'index']);
            Route::GET('/show', [OrderController::class, 'show']);
            Route::post('/change-status',  [OrderController::class, 'updateStatus']);
            Route::post('/assign',  [OrderController::class, 'assign']);
        });

        Route::prefix('cart')->group(function(){
            Route::GET('/', [CartController::class, 'cart']);
            Route::POST('/add-to-cart', [CartController::class, 'add_to_cart']);
            Route::POST('/remove', [CartController::class, 'remove_from_cart']);
            Route::POST('/empty', [CartController::class, 'empty_cart']);
            Route::POST('/edit-quantity', [CartController::class, 'edit_quantity']);
            Route::POST('/checkout', [CartController::class, 'checkout']);
        });

    });
});
