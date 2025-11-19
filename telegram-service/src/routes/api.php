<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('internal/telegram/webhook', [TelegramBotController::class, 'handle']);
Route::post('/setwebhook', function () {
    $response = Telegram::setWebhook(['url' => 'https://promobank.io/api/telegram/webhook']);
    Log::info('Webhook set: ' . $response);
    return response()->json(['data' => $response]);
});
