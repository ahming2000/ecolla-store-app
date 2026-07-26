<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\ItemImageController;
use App\Http\Controllers\Admin\ItemVariationController;
use App\Http\Controllers\Admin\ItemVariationImageController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OriginController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WikiController;
use App\Http\Controllers\Shop\CategoryController;
use App\Models\Item;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ajax Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/ajax/admin')->name('admin.ajax.')->group(function () {
    Route::prefix('/item')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('item.index');
        Route::post('/', [ItemController::class, 'store'])->name('item.store');
        Route::post('/{item}/image/{image}', [ItemImageController::class, 'store'])
            ->middleware('auth')
            ->name('item.image.store');
        Route::delete('/{item}/image/{image}', [ItemImageController::class, 'destroy'])
            ->middleware('auth')
            ->name('item.image.destroy');
        Route::patch('/{item}/view-count/reset', [ItemController::class, 'resetViewCount'])
            ->middleware('auth')
            ->name('item.view-count.reset');
        Route::patch('/{item}/sold-count/reset', [ItemController::class, 'resetSoldCount'])
            ->middleware('auth')
            ->name('item.sold-count.reset');
        Route::patch('/{item}/listing', [ItemController::class, 'updateListing'])
            ->middleware('auth')
            ->name('item.listing.update');
        Route::put('/{item}', [ItemController::class, 'update'])
            ->middleware('auth')
            ->name('item.update');
        Route::delete('/{item}', [ItemController::class, 'destroy'])
            ->middleware('auth')
            ->can('delete', 'item')
            ->name('item.destroy');

        Route::prefix('/{item}/variation')
            ->name('item.variation.')
            ->middleware(['auth', 'can:update,item'])
            ->scopeBindings()
            ->group(function () {
                Route::post('/', [ItemVariationController::class, 'store'])
                    ->name('store');
                Route::put('/{variation}', [ItemVariationController::class, 'update'])
                    ->name('update');
                Route::delete('/{variation}', [ItemVariationController::class, 'destroy'])
                    ->name('destroy');
                Route::post('/{variation}/image', [ItemVariationImageController::class, 'store'])
                    ->name('image.store');
                Route::delete('/{variation}/image', [ItemVariationImageController::class, 'destroy'])
                    ->name('image.destroy');
            });
    });

    Route::prefix('/order')
        ->middleware('auth')
        ->group(function () {
            Route::get('/', [OrderController::class, 'index'])
                ->name('order.index')
                ->can('viewAny', Order::class);
            Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])
                ->name('order.status.update');
            Route::patch('/{order}/tracking-number', [OrderController::class, 'updateTrackingNumber'])
                ->name('order.tracking-number.update');
        });

    Route::prefix('/category')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('category.index');
    });

    Route::prefix('/origin')->group(function () {
        Route::get('/', [OriginController::class, 'index'])->name('origin.index');
    });

    Route::prefix('/user')->middleware('auth')->group(function () {
        Route::post('/', [UserController::class, 'create'])->name('user.create');
        Route::put('/{user}', [UserController::class, 'update'])->name('user.update');
        Route::patch('/{user}/deactivate', [UserController::class, 'deactivate'])
            ->can('deactivate', 'user')
            ->name('user.deactivate');
        Route::patch('/{user}/reactivate', [UserController::class, 'reactivate'])
            ->can('reactivate', 'user')
            ->name('user.reactivate');
        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->can('delete', 'user')
            ->name('user.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Page Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'loginPage'])->name('login.page');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
});

Route::prefix('/admin')->name('admin.')->group(function () {
    Route::get('/changing-log', [AdminController::class, 'changingLogPage'])->name('changing-log.page');

    Route::middleware('auth')->group(function () {
        Route::put('/lang', [LanguageController::class, 'update'])->name('lang.update');
        Route::get('/', [DashboardController::class, 'page'])->name('dashboard.page');
        Route::get('/profile', [UserController::class, 'profilePage'])->name('profile.page');
        Route::get('/wiki', [WikiController::class, 'page'])->name('wiki.page');
        Route::get('/item', [ItemController::class, 'page'])->name('item.page')->can('viewAny', Item::class);
        Route::get('/order', [OrderController::class, 'page'])->name('order.page')->can('viewAny', Order::class);
        Route::get('/order/{order}/download', [OrderController::class, 'download'])
            ->name('order.download')
            ->can('view', 'order');
        Route::get('/user', [UserController::class, 'page'])->name('user.page')->can('viewAny', User::class);
        Route::get('/setting', [AdminController::class, 'settingPage'])->name('setting.page')->can('viewAny', Setting::class);
        Route::patch('/setting/shipping', [SettingController::class, 'updateShippingFee'])
            ->name('setting.shipping.update');
        Route::patch('/setting/free-shipping', [SettingController::class, 'updateFreeShipping'])
            ->name('setting.free-shipping.update');
    });

    Route::get('/{any}', function (): never {
        abort(404);
    })->where('any', '.*');
});
