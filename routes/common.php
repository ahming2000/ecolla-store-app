<?php

use App\Http\Controllers\Image\ImageController;
use Illuminate\Support\Facades\Route;

Route::prefix('/ajax')->group(function () {
    Route::post('/image/upload', [ImageController::class, 'upload'])->name('image.upload');
});
