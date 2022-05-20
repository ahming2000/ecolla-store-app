<?php


use App\Http\Controllers\CartController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SystemConfigController;
use Illuminate\Support\Facades\Route;

Route::prefix('/cart')->group(function () {
    Route::get('/count', [CartController::class, 'count']);

    Route::post('/add', [CartController::class, 'add']);
    Route::post('/remove', [CartController::class, 'remove']);
    Route::post('/reset', [CartController::class, 'reset']);
    Route::post('/update-quantity', [CartController::class, 'updateQuantity']);
    Route::post('/update-order-mode', [CartController::class, 'updateOrderMode']);
});

Route::prefix('/image')->group(function () {
    Route::post('/verify', [ImageController::class, 'verify']);
});

Route::prefix('/system-config')->group(function () {
    Route::get('/shipping-fee-config', [SystemConfigController::class, 'shippingFeeConfig']);
});
