<?php

use App\Http\Controllers\Mobil\GameController;
use Illuminate\Support\Facades\Route;

Route::controller(GameController::class)->group(function () {
    Route::post('/info', 'index');
    Route::post('/start', 'start');
});
