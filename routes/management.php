<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'loginPage'])->middleware('guest')->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/changing-log', [InfoController::class, 'changingLogPage']);

Route::middleware('auth')->group(function () {
    Route::get('/', [InfoController::class, 'dashboardPage']);
    Route::get('/profile', [UserController::class, 'profilePage']);

    Route::prefix('/item')->group(function () {
        Route::get('/', [ItemController::class, 'managePage']);
        Route::get('/edit', [ItemController::class, 'editPage']);

        Route::post('/', [ItemController::class, 'create']);
        Route::patch('/{item}', [ItemController::class, 'update']);
        Route::delete('/{item}', [ItemController::class, 'delete']);
    });

    Route::prefix('/order')->group(function () {
        Route::get('/', [OrderController::class, 'managePage']);
        Route::get('/{order}', [OrderController::class, 'singlePage']);

        Route::post('/', [OrderController::class, 'create']);
        Route::patch('/{order}', [OrderController::class, 'update']);
        Route::delete('/{order}', [OrderController::class, 'delete']);
    });

    Route::prefix('/user')->group(function () {
        Route::get('/', [UserController::class, 'managePage']);

        Route::post('/', [UserController::class, 'create']);
        Route::patch('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'delete']);
    });

    Route::get('/setting', [SettingController::class, 'settingPage']);
});
