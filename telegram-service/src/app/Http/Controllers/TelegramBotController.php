<?php
namespace App\Http\Controllers;

use App\Telegram\Middleware\EnsureTelegramSessionExists;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    public function handle(Request $request)
    {
        $rawUpdate = request('__internal_update') ?? Telegram::getWebhookUpdate();
        if ($rawUpdate instanceof \Illuminate\Support\Collection) {
            $rawUpdate = new \Telegram\Bot\Objects\Update($rawUpdate->toArray());
        }
        $update = $rawUpdate;
        $middlewareResult = app(EnsureTelegramSessionExists::class)->handle($update);
        if ($middlewareResult) {
            return $middlewareResult;
        }
        return response()->noContent();
    }
}
