<?php

use App\Http\Controllers\Mobil\AddresController;
use App\Http\Controllers\Mobil\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/guest', 'guest');
    Route::post('/login', 'login')->middleware(['guestCheck']);
    Route::post('/check/{id}', 'check')->middleware(['guestCheck']);
    // Route::get('/user', 'user')->middleware(['guestCheck']);
    Route::post('/register', 'register')->middleware(['guestCheck']);
    Route::put('/user_update', 'userupdate')->middleware(['guestCheck']);
    Route::post('/check_update', 'checkUpdate')->middleware(['guestCheck']);
    // Route::post('/resendsms/{id}', 'resendsms');
    // Route::post('/logout', 'logout')->middleware(['guestCheck']);
});

Route::controller(AddresController::class)->middleware(['guestCheck'])->group(function () {
    Route::get('/regions', 'region');
    Route::get('/regions/{region_id}', 'district');
});