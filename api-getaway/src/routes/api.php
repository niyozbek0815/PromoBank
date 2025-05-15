<?php

use App\Http\Controllers\Mobil\AddresController;
use App\Http\Controllers\Mobil\AuthController;
use App\Http\Controllers\Mobil\PromoController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/guest-login', 'guest');
    Route::post('/login', 'login')->middleware(['guestCheck']);
    Route::post('/register', 'register')->middleware(['guestCheck']);
    Route::post('/verifications/{id}', 'check')->middleware(['guestCheck']);
    Route::get('/me', 'user')->middleware(['guestCheck']);
    Route::put('/me', 'userupdate')->middleware(['guestCheck']);
    Route::post('/me/verify-update', 'checkUpdate')->middleware(['guestCheck']);

    // Route::post('/verifications/{id}/resend', 'resendsms')->middleware('guestCheck'); // POST /auth/verifications/{id}/resend
    // Route::post('/logout', 'logout')->middleware('guestCheck'); // POST /auth/logout
});

Route::controller(AddresController::class)->middleware(['guestCheck'])->group(function () {
    Route::get('/regions', 'region');
    Route::get('/regions/{region_id}/districts', 'district');
});
Route::controller(PromoController::class)->prefix('promotions')->middleware(['guestCheck'])->group(function () {
    Route::get('/', 'index');
    Route::post('/{promotion}/participate/promocode', 'viaPromocode');
    Route::post('/{promotion}/participate/receipt', 'viaReceipt');
    Route::get('/{promotion}/participations', 'listParticipationHistory');
});
