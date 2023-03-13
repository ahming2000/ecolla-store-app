<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{lang}', [SettingController::class, 'changeLanguage'])->name('changeLanguage');

Route::get('/', [InfoController::class, 'landingPage'])->name('landingPage');
Route::get('/payment-method', [InfoController::class, 'paymentMethodPage'])->name('paymentMethodPage');

Route::prefix('/item')->group(function () {
   Route::get('/', [ItemController::class, 'listingPage'])->name('listingPage');
   Route::get('/{item}', [ItemController::class, 'singleListingPage'])->name('singleListingPage');
});

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'cartPage'])->name('cartPage');
    Route::get('/check-out', [CartController::class, 'checkOutPage'])->name('checkOutPage');

    Route::post('/check-out', [CartController::class, 'checkOut'])->name('checkOut');
});
