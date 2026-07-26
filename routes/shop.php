<?php

use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CategoryController;
use App\Http\Controllers\Shop\ItemController;
use App\Http\Controllers\Shop\OriginController;
use App\Http\Controllers\Shop\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ajax Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/ajax')->name('shop.ajax.')->group(function () {
    Route::prefix('/item')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('item.index');
        Route::get('/{item:slug}', [ItemController::class, 'show'])->name('item.show');
    });

    Route::get('/category', [CategoryController::class, 'index'])->name('category.index');

    Route::get('/origin', [OriginController::class, 'index'])->name('origin.index');

    Route::get('/payment-method', [ShopController::class, 'getAllPaymentMethods'])->name('payment-method.index');

    Route::post('/cart/verify', [CartController::class, 'verifyCart'])->name('cart.verify');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
});

/*
|--------------------------------------------------------------------------
| Page Routes
|--------------------------------------------------------------------------
*/
Route::name('shop.')->group(function () {
    Route::get('/', [ShopController::class, 'landingPage'])->name('landing.page');

    Route::get('/item', [ItemController::class, 'page'])->name('item.page');
    Route::get('/item/{item:slug}', [ItemController::class, 'show'])->name('item.show');

    Route::get('/cart', [CartController::class, 'cartPage'])->name('cart.page');
    Route::get('/checkout', [CartController::class, 'checkoutPage'])->name('cart.checkout-page');
    Route::get('/checkout-successful/{order}', [CartController::class, 'checkoutSuccessfulPage'])->name('cart.successful-page');

    Route::get('/payment-method', [ShopController::class, 'paymentMethodPage'])->name('payment-method.page');

    Route::get('/{any}', function (): never {
        abort(404);
    })->where('any', '.*');
});
