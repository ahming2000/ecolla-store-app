<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{lang}', [SettingController::class, 'changeLanguage']);

Route::get('/', [InfoController::class, 'landingPage']);
Route::get('/payment-method', [InfoController::class, 'paymentMethodPage']);

Route::prefix('/item')->group(function () {
   Route::get('/', [ItemController::class, 'listingPage']);
   Route::get('/{item}', [ItemController::class, 'singleListingPage']);
});

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'cartPage']);
    Route::get('/check-out', [CartController::class, 'checkOutPage']);

    Route::post('/check-out', [CartController::class, 'checkOut']);
});
