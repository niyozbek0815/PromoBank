<?php

use App\Http\Controllers\Mobil\GameController;
use Illuminate\Support\Facades\Route;

Route::controller(GameController::class)->group(function () {
    Route::post('/info', 'index');
    Route::post('/start_next', 'startNext');
    Route::post('/open_cards', 'openCards');
    Route::post('/refresh_game', 'refreshGame');
});
