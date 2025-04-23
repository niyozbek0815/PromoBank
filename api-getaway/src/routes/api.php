<?php

use App\Http\Controllers\Mobil\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/guest', 'guest');
    Route::post('/login', 'login')->middleware(['guestCheck']);
    Route::post('/check/{id}', 'check');
    Route::get('/user', 'user')->middleware('auth:sanctum', 'check.status');
    Route::post('/register', 'register')->middleware('auth:sanctum', 'check.status');
    Route::put('/user_update', 'userupdate')->middleware('auth:sanctum', 'check.status');
    Route::post('/check_update', 'checkUpdate')->middleware('auth:sanctum', 'check.status');
    // Route::post('/resendsms/{id}', 'resendsms');
    Route::post('/logout', 'logout')->middleware('auth:sanctum', 'check.status');
});
