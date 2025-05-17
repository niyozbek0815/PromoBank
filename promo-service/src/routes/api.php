<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobil\PromoController;
use App\Http\Controllers\ReceiptController;

Route::controller(PromoController::class)->prefix('promotions')->group(function () {
    Route::get('/', 'index');
    Route::post('/{promotion}/participate/promocode', 'viaPromocode');
    Route::post('/{promotion}/participate/receipt', 'viaReceipt');
    Route::post('/{promotion}/participations', 'listParticipationHistory');
});
Route::controller(ReceiptController::class)->prefix('receipt')->group(function () {
    Route::post('/', 'index');
});
