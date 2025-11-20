<?php
namespace App\Telegram\Handlers\Start;

use App\Telegram\Services\RegisterRouteService;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartHandler
{
    public function __construct(
        protected Translator $translator
    ) {
        // Constructor can be used for dependency injection if needed
    }


public function startAsk($chatId)
{
    Log::info("StartHandler startAsk ishladi: $chatId");

    try {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'start'),
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
            ]),
        ]);
    } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'bot was blocked by the user') || str_contains($msg, 'message is not modified')) {
            Log::warning("Telegram xabari yuborilmadi, foydalanuvchi bloklagan yoki o'zgartirish yo'q: $chatId, msg: $msg");
            return; // shunchaki return qilamiz, xatolik qaytarmaydi
        }
        Log::warning("12Telegram xabari yuborilmadi: $chatId, msg: $msg");

      return;
    }

    app(RegisterService::class)->mergeToCache($chatId, [
        'chat_id' => $chatId,
        'state' => 'waiting_for_language',
    ]);

    return app(RegisterRouteService::class)->askNextStep($chatId);
}

public function ask($chatId)
{
    try {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'welcome'),
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
            ]),
        ]);
    } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'bot was blocked by the user') || str_contains($msg, 'message is not modified')) {
            Log::warning("Telegram xabari yuborilmadi, foydalanuvchi bloklagan yoki o'zgartirish yo'q: $chatId, msg: $msg");
            return;
        }
        throw $e;
    }

    $lang = json_decode(Cache::store('bot')->get("tg_lang:$chatId"), true) ?? [];

    if (empty($lang)) {
        try {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❗️ Iltimos, tilni tanlang.\n❗️ Пожалуйста, выберите язык.\n❗️ Илтимос, тилни танланг.\n❗️ Please, select your language.",
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => $this->translator->getForLang('language_selection', 'uz'),
                                'callback_data' => 'lang:uz',
                            ],
                            [
                                'text' => $this->translator->getForLang('language_selection', 'ru'),
                                'callback_data' => 'lang:ru',
                            ],
                        ],
                        [
                            [
                                'text' => $this->translator->getForLang('language_selection', 'kr'),
                                'callback_data' => 'lang:kr',
                            ],
                            [
                                'text' => $this->translator->getForLang('language_selection', 'en'),
                                'callback_data' => 'lang:en',
                            ],
                        ],
                    ],
                ]),
            ]);
        } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'bot was blocked by the user') || str_contains($msg, 'message is not modified')) {
                Log::warning("Telegram xabari yuborilmadi, foydalanuvchi bloklagan yoki o'zgartirish yo'q: $chatId, msg: $msg");
                return;
            }
            throw $e;
        }
        return;
    }

    return app(RegisterRouteService::class)->askNextStep($chatId);
}}
