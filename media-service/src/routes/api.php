<?php

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('media')->group(function () {
    Route::post('upload', [MediaController::class, 'upload']);
    Route::delete('{collection}/{filename}', [MediaController::class, 'destroy']);
});