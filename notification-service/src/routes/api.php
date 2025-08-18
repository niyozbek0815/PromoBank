<?php

use App\Http\Controllers\NotificationsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::prefix('front')->group(function () {
    Route::prefix('notifications')->name('admin.notifications.')
        ->controller(NotificationsController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{banner}/edit', 'edit')->name('edit');
            Route::put('/{banner}', 'update')->name('update');
            Route::post('/{banner}/delete', 'destroy')->name('delete');
            Route::post('/{notification}/resent', 'resent')->name('resent');
            Route::get('/{type}/urls', 'getUrls')->name('getUrls');
        });

});
