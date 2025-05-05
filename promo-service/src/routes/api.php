<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobil\PromoController;


Route::controller(PromoController::class)->group(function () {
    Route::get('/promotions', 'index');
});
