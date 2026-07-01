<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user/me', [UserController::class, 'me']);
    Route::post('/users/{id}/role', [UserController::class, 'assignRole'])
        ->middleware('role:admin');

    Route::middleware('role:admin,manager')->group(function () {
        Route::apiResource('products', ProductController::class);
        Route::post('/products/{id}/stock', [ProductController::class, 'updateStock']);
    });

    Route::middleware('role:admin,manager,cashier')->group(function () {
        Route::post('/sales', [SaleController::class, 'store']);
        Route::get('/sales/{id}', [SaleController::class, 'show']);
        Route::post('/sales/{id}/complete', [SaleController::class, 'complete']);
        Route::post('/sales/{id}/cancel', [SaleController::class, 'cancel']);
        Route::get('/sales/{id}/coupon', [SaleController::class, 'coupon']);
    });
});
