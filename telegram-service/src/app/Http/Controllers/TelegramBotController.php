<?php

namespace App\Http\Controllers;

use App\Telegram\Handlers\CallbackHandler;
use App\Telegram\Handlers\ContactHandler;
use App\Telegram\Handlers\SendPhoneRequest;
use App\Telegram\Handlers\StartHandler;
use App\Telegram\Middleware\EnsureTelegramSessionExists;
use App\Telegram\Services\UserSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    public function handle(Request $request)
    {
        $update = request('__internal_update') ?? Telegram::getWebhookUpdate();
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        $openRoutes = ['/start'];
        $messageText = $update->getMessage()?->getText();
        $isOpenRoute = in_array($messageText, $openRoutes)
            || $update->getCallbackQuery()
            || $update->getMessage()?->getContact();

        $sessionService = app(UserSessionService::class);

        if (! $sessionService->exists($chatId) && ! $isOpenRoute) {
            if (!$update->getMessage()?->getContact() && $messageText) {
                Log::info($messageText . " Cachega yozildi");
                Cache::store('redis')->put("tg_pending:$chatId", $messageText, now()->addMinutes(5));
            }
            Cache::store('redis')->forget("tg_pending:$chatId");
            return app(SendPhoneRequest::class)->handle($chatId);
        }
        if ($update->getMessage()?->getContact()) {
            return app(ContactHandler::class)->handle($update->getMessage());
        }
        if ($callback = $update->getCallbackQuery()) {
            return app(CallbackHandler::class)->handle($callback);
        }

        // Agar /start komandasi yuborilgan bo‘lsa
        if ($update->getMessage()?->getText() === '/start') {
            return app(StartHandler::class)->handle($chatId);
        }

        // Agar user contact yuborgan bo‘lsa

        if ($update->getMessage()?->getText() === 'Salom') {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "Assalom alaykum",
            ]);
        }
        // Default javob


        return response()->noContent();
    }


    // Bot uchun shartlar yozish agar manu shu chat isli user
    //  bo'lmasa uni yarat yoki yaratishb tugmasi bos. Yani web apdagi kabi register tizimini qurishim kerak
}