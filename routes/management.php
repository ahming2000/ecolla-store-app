<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/changing-log', [InfoController::class, 'changingLogPage']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', [InfoController::class, 'dashboardPage']);
    Route::get('/profile', [UserController::class, 'profilePage']);

    Route::patch('/profile', [UserController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('/item')->group(function () {
        Route::get('/', [ItemController::class, 'managePage']);
        Route::get('/{item}', [ItemController::class, 'singlePage']);

        Route::middleware('can:employee')->group(function () {
            Route::get('/edit', [ItemController::class, 'editPage']);
            Route::patch('/{item}', [ItemController::class, 'update']);
        });

        Route::middleware('can:manager')->group(function () {
            Route::post('/', [ItemController::class, 'create']);
            Route::delete('/{item}', [ItemController::class, 'delete']);
        });
    });

    Route::prefix('/order')->group(function () {
        Route::get('/', [OrderController::class, 'managePage']);
        Route::get('/{order}', [OrderController::class, 'singlePage']);

        Route::middleware('can:employee')->group(function () {
            Route::patch('/{order}', [OrderController::class, 'update']);
        });

        Route::middleware('can:manager')->group(function () {
            Route::post('/', [OrderController::class, 'create']);
            Route::delete('/{order}', [OrderController::class, 'delete']);
        });
    });

    Route::prefix('/user')->middleware('can:admin')->group(function () {
        Route::get('/', [UserController::class, 'managePage']);

        Route::post('/', [UserController::class, 'create']);
        Route::patch('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'delete']);
    });

    Route::prefix('/setting')->middleware('can:admin')->group(function () {
        Route::get('/', [SettingController::class, 'settingPage']);
    });
});
