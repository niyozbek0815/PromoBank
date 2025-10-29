<?php

use App\Http\Controllers\Mobil\GameController;
use Illuminate\Support\Facades\Route;

Route::controller(GameController::class)->group(function () {
    Route::post('/info', 'index');
    Route::post('/start_next', 'startNext');
    Route::post('/open_cards', 'openCards');
    Route::post('/open_cards_final', 'openCardsFinal');
    Route::post('/reject-stage2', 'rejectStage2');
    Route::get('/front/games/gettypes', 'getTypes');
    Route::post('/start_two', 'startTwo');
});
