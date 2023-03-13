<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{lang}', [SettingController::class, 'changeLanguage'])->name('listing.language.update');

Route::get('/', [InfoController::class, 'landingPage'])->name('listing.landing');
Route::get('/payment-method', [InfoController::class, 'paymentMethodPage'])->name('listing.payment.index');

Route::prefix('/item')->group(function () {
   Route::get('/', [ItemController::class, 'listingPage'])->name('listing.item.index');
   Route::get('/{item}', [ItemController::class, 'singleListingPage'])->name('listing.item.view');
});

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'cartPage'])->name('listing.cart.index');
    Route::get('/check-out', [CartController::class, 'checkOutPage'])->name('listing.cart.checkOut.index');

    Route::post('/check-out', [CartController::class, 'checkOut'])->name('listing.cart.checkOut');
});
