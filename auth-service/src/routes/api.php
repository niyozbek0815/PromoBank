<?php

use App\Http\Controllers\Mobil\AddresController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobil\AuthController;



Route::controller(AuthController::class)->group(function () {
    Route::post('/guest', 'guest');
    Route::post('/refresh-token', 'refreshToken');
    Route::post('/login', 'login');
    Route::post('/check/{id}', 'check');
    Route::get('/user', 'user');
    Route::post('/register', 'register');
    Route::put('/user_update', 'userupdate');
    Route::post('/check_update', 'checkUpdate');
    Route::get('/user', 'user');

    // Route::post('/resendsms/{id}', 'resendsms');
    // Route::post('/logout', 'logout');
});
Route::controller(AddresController::class)->group(function () {
    Route::get('/regions', 'region');
    Route::get('/regions/{region_id}', 'district');
});
