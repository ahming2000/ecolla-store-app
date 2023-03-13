<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/setup', [SystemController::class, 'freshSetup'])->name('management.freshSetup');
Route::get('/system-update/{password}', [SystemController::class, 'systemUpdate'])->name('management.systemUpdate');

Route::get('/changing-log', [InfoController::class, 'changingLogPage'])->name('management.changingLog.index');

Route::get('/login', [AuthController::class, 'loginPage'])->name('management.login.index')->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::get('/', [InfoController::class, 'dashboardPage'])->name('management.dashboard');
    Route::get('/profile', [UserController::class, 'profilePage'])->name('management.profile.index');

    Route::patch('/profile', [UserController::class, 'updatePassword'])->name('management.profile.update');

    Route::prefix('/item')->group(function () {
        Route::get('/', [ItemController::class, 'managePage'])->name('management.item.index');
        Route::get('/{item}', [ItemController::class, 'singlePage'])->name('management.item.view');

        Route::middleware('can:employee')->group(function () {
            Route::get('/edit', [ItemController::class, 'editPage'])->name('management.item.edit');
            Route::patch('/{item}', [ItemController::class, 'update'])->name('management.item.update');
        });

        Route::middleware('can:manager')->group(function () {
            Route::post('/', [ItemController::class, 'create'])->name('management.item.create');
            Route::delete('/{item}', [ItemController::class, 'delete'])->name('management.item.delete');
        });
    });

    Route::prefix('/order')->group(function () {
        Route::get('/', [OrderController::class, 'managePage'])->name('management.order.index');
        Route::get('/{order}', [OrderController::class, 'singlePage'])->name('management.order.view');

        Route::middleware('can:employee')->group(function () {
            Route::patch('/{order}', [OrderController::class, 'update'])->name('management.order.update');
        });

        Route::middleware('can:manager')->group(function () {
            Route::post('/', [OrderController::class, 'create'])->name('management.order.create');
            Route::delete('/{order}', [OrderController::class, 'delete'])->name('management.order.delete');
        });
    });

    Route::prefix('/user')->middleware('can:admin')->group(function () {
        Route::get('/', [UserController::class, 'managePage'])->name('management.user.index');

        Route::post('/', [UserController::class, 'create'])->name('management.user.create');
        Route::patch('/{user}', [UserController::class, 'update'])->name('management.user.update');
        Route::delete('/{user}', [UserController::class, 'delete'])->name('management.user.delete');
    });

    Route::prefix('/setting')->middleware('can:admin')->group(function () {
        Route::get('/', [SettingController::class, 'settingPage'])->name('management.setting.index');
    });
});
