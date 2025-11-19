<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/setwebhook', function () {
    $response = Telegram::setWebhook(['url' => 'https://promobank.io/api/telegram/webhook']);
    Log::info('Webhook set: ' . $response);
});
