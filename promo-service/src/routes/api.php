<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobil\PromoController;


Route::controller(PromoController::class)->prefix('promotions')->group(function () {
    Route::get('/', 'index');
    Route::post('/{promotion}/participate/promocode', 'viaPromocode');
    Route::post('/{promotion}/participate/receipt', 'viaReceipt');
    Route::get('/{promotion}/participation-status', 'checkStatus');
});
