<?php

use App\Http\Controllers\Mobil\AddresController;
use App\Http\Controllers\Mobil\AuthController;
use App\Http\Controllers\Mobil\BannerController;
use App\Http\Controllers\Mobil\GameController;
use App\Http\Controllers\Mobil\GetawayGameController;
use App\Http\Controllers\Mobil\PromoController;
use App\Http\Controllers\Mobil\ReceiptController;
use App\Http\Controllers\Sms\PromoSmsGatewayController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/guest-login', 'guest');
    Route::post('/login', 'login')->middleware(['guestCheck']);
    Route::post('/register', 'register')->middleware(['guestCheck']);
    Route::post('/verifications/{id}', 'check')->middleware(['guestCheck']);
    Route::get('/me', 'user')->middleware(['guestCheck:false']);
    Route::put('/me', 'userupdate')->middleware(['guestCheck:false']);
    Route::post('/me/verify-update', 'checkUpdate')->middleware(['guestCheck:false']);
    // Route::post('/verifications/{id}/resend', 'resendsms')->middleware('guestCheck'); // POST /auth/verifications/{id}/resend
    // Route::post('/logout', 'logout')->middleware('guestCheck'); // POST /auth/logout
});

Route::controller(AddresController::class)->middleware(['guestCheck'])->group(function () {
    Route::get('/regions', 'region');
    Route::get('/regions/{region_id}/districts', 'district');
});
Route::controller(PromoController::class)->prefix('promotions')->group(function () {
    Route::get('/', 'index')->middleware(['guestCheck']);
    Route::post('/{promotion}/participate/promocode', 'viaPromocode')->middleware(['guestCheck:false']);
    Route::post('/{promotion}/participate/receipt', 'viaReceipt')->middleware(['guestCheck:false']);
    Route::get('/{promotion}/participations', 'listParticipationHistory')->middleware(['guestCheck:false']);
});
Route::controller(BannerController::class)->prefix('banners')->middleware(['guestCheck'])->group(function () {
    Route::get('/', 'index');
});

Route::controller(ReceiptController::class)->prefix('receipt')->middleware(['guestCheck'])->group(function () {
    Route::post('/', 'index')->middleware(['guestCheck:false']);
    Route::get('/user_points', 'points')->middleware(['guestCheck:false']);
});
Route::prefix('games')->middleware(['guestCheck'])->group(function () {
    Route::get('/', [GameController::class, 'listAllGames']);
    Route::any('/{game}/{action}', [GetawayGameController::class, 'handle'])->middleware(['guestCheck:false']);
});
// Sms orqali promocode jo'natish uchun api
Route::post('/sms/promo/receive-sms', [PromoSmsGatewayController::class, 'receive'])->middleware(['smsProvider']);
