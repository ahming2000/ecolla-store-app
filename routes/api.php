<?php


use App\Http\Controllers\CartController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SystemController;
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
    Route::get('/shipping-fee-config', [SystemController::class, 'shippingFeeConfig']);
});

Route::prefix('/setting')->middleware(['auth', 'can:manager'])->group(function () {
    Route::prefix('/origin')->group(function () {
        Route::post('/', [SettingController::class, 'addOrigin']);
        Route::patch('/{origin}', [SettingController::class, 'updateOrigin']);
        Route::delete('/{origin}', [SettingController::class, 'deleteOrigin']);
    });

    Route::prefix('/category')->group(function () {
        Route::post('/', [SettingController::class, 'addCategory']);
        Route::patch('/{category}', [SettingController::class, 'updateCategory']);
        Route::delete('/{category}', [SettingController::class, 'deleteCategory']);
    });

    Route::prefix('shipping')->group(function () {
        Route::patch('/fee', [SystemController::class, 'updateShippingFee']);
        Route::patch('/discount', [SystemController::class, 'updateShippingDiscount']);
    });
});
