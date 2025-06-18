<?php
namespace App\Telegram\Handlers\Start;

use App\Telegram\Handlers\Register\SendPhoneRequest;
use App\Telegram\Services\RegisterRouteService;
use App\Telegram\Services\RegisterService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartHandler
{
    public function ask($chatId)
    {
        $tg_user_data = json_decode(Cache::store('redis')->get("tg_user_data:$chatId"), true) ?? [];

        if (empty($tg_user_data)) {
            Telegram::sendMessage([
                'chat_id'      => $chatId,
                'text'         => "🌐 Iltimos, tilni tanlang:\n🌐 Пожалуйста, выберите язык:\n🌐 Илтимос, тилни танланг:",
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => "🇺🇿 O‘zbekcha", 'callback_data' => 'lang:uz'],
                            ['text' => "🇷🇺 Русский", 'callback_data' => 'lang:ru'],
                            ['text' => "🇺🇿 Кирилл", 'callback_data' => 'lang:kr'],
                        ],
                    ],
                ]),
            ]);
            return;
        }

        return app(RegisterRouteService::class)->askNextStep($chatId);

    }

    public function handle($update)
    {
        $messageText = $update->getCallbackQuery()?->getData();

        $chatId = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        $callbackMessage = $update->getCallbackQuery()?->getMessage();

        if ($callbackMessage) {
            Telegram::deleteMessage([
                'chat_id'    => $chatId,
                'message_id' => $callbackMessage->getMessageId(),
            ]);
        }

        $lang = str_replace('lang:', '', $messageText);
        Log::info("StartHandler handle ishladi: $lang");

        app(RegisterService::class)->mergeToCache($chatId, [
            'chat_id' => $chatId,
            'lang'    => $lang,
            'state'   => 'waiting_for_phone',
        ]);
        return app(SendPhoneRequest::class)->ask($chatId);
    }
}