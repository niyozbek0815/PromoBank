<?php

use App\Http\Controllers\Mobil\AddresController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobil\AuthController;



Route::controller(AuthController::class)->group(function () {
    Route::post('/refresh-token', 'refreshToken');
    Route::post('/guest-login', 'guest');
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/verifications/{id}', 'check');
    Route::get('/me', 'user');
    Route::put('/me', 'userupdate');
    Route::post('/me/verify-update', 'checkUpdate');


    Route::post('/users_for_sms', 'userForSms');

    // Route::post('/verifications/{id}/resend', 'resendsms')->middleware('guestCheck'); // POST /auth/verifications/{id}/resend
    // Route::post('/logout', 'logout')->middleware('guestCheck'); // POST /auth/logout
});
Route::controller(AddresController::class)->group(function () {
    Route::get('/regions', 'region');
    Route::get('/regions/{region_id}/districts', 'district');
});
