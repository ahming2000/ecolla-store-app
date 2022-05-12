<?php


use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::prefix('/cart')->group(function () {
    Route::get('/count', [CartController::class, 'count']);
    Route::post('/add', [CartController::class, 'add']);
    Route::post('/remove', [CartController::class, 'remove']);
    Route::post('/reset', [CartController::class, 'reset']);
    Route::post('/update-quantity', [CartController::class, 'updateQuantity']);
    Route::post('/update-order-mode', [CartController::class, 'updateOrderMode']);
    Route::post('/update-customer-data', [CartController::class, 'updateCustomerData']);
});
