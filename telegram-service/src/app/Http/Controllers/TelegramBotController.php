<?php
namespace App\Http\Controllers;

use App\Telegram\Middleware\EnsureTelegramSessionExists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    public function handle(Request $request)
    {
        $update = request('__internal_update') ?? Telegram::getWebhookUpdate();
        Log::info("Controller ishladi");
        $middlewareResult = app(EnsureTelegramSessionExists::class)->handle($update);
        if ($middlewareResult) {
            Log::info("Controller middleware ishladi");
            return $middlewareResult;
        }

        // Dispatching logic based on message content
        // if ($update->getMessage()?->getContact()) {
        //     return app(ContactHandler::class)->handle($update);
        // }

        // if ($callback = $update->getCallbackQuery()) {
        //     return app(CallbackHandler::class)->handle($callback);
        // }

        // if ($messageText === '/start') {
        //     return app(StartHandler::class)->ask($chatId);
        // }

        // if ($messageText === 'Salom') {
        //     Telegram::sendMessage([
        //         'chat_id' => $chatId,
        //         'text'    => "Assalom alaykum",
        //     ]);
        // }

        return response()->noContent();
    }

    // Bot uchun shartlar yozish agar manu shu chat isli user
    //  bo'lmasa uni yarat yoki yaratishb tugmasi bos. Yani web apdagi kabi register tizimini qurishim kerak
}
