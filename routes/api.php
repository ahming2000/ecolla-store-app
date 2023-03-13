<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('api.login')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout')->middleware('auth');

Route::prefix('/cart')->group(function () {
    Route::get('/count', [CartController::class, 'count'])->name('api.cart.count');

    Route::post('/add', [CartController::class, 'add'])->name('api.cart.add');
    Route::post('/remove', [CartController::class, 'remove'])->name('api.cart.remove');
    Route::post('/reset', [CartController::class, 'reset'])->name('api.cart.reset');
    Route::post('/quantity', [CartController::class, 'setQuantity'])->name('api.cart.quantity.update');
    Route::post('/order-mode', [CartController::class, 'setOrderMode'])->name('api.cart.orderMode.update');
});

Route::prefix('/image')->group(function () {
    Route::post('/verify', [ImageController::class, 'verify'])->name('api.image.verify');
});

Route::prefix('/system-config')->group(function () {
    Route::get('/shipping-fee-config', [SystemController::class, 'shippingFeeConfig'])->name('api.config.shippingFee');
});

Route::prefix('/setting')->middleware(['auth', 'can:manager'])->group(function () {
    Route::prefix('/origin')->group(function () {
        Route::post('/', [SettingController::class, 'addOrigin'])->name('api.setting.origin.create');
        Route::patch('/{origin}', [SettingController::class, 'updateOrigin'])->name('api.setting.origin.update');
        Route::delete('/{origin}', [SettingController::class, 'deleteOrigin'])->name('api.setting.origin.delete');
    });

    Route::prefix('/category')->group(function () {
        Route::post('/', [SettingController::class, 'addCategory'])->name('api.setting.category.create');
        Route::patch('/{category}', [SettingController::class, 'updateCategory'])->name('api.setting.category.update');
        Route::delete('/{category}', [SettingController::class, 'deleteCategory'])->name('api.setting.category.delete');
    });

    Route::prefix('shipping')->group(function () {
        Route::patch('/fee', [SystemController::class, 'updateShippingFee'])->name('api.setting.shipping.fee');
        Route::patch('/discount', [SystemController::class, 'updateShippingDiscount'])->name('api.setting.shipping.discount');
    });
});
