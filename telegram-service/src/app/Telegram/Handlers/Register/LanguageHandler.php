<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class LanguageHandler
{
    public function __construct(
        protected Translator $translator
    ) {}

    public function ask($chatId)
    {
        $text     = "🌐 Iltimos, tilni tanlang:\n🌐 Пожалуйста, выберите язык:\n🌐 Илтимос, тилни танланг:";
        $keyboard = [
            [
                ['text' => "🇺🇿 O‘zbekcha", 'callback_data' => 'lang:uz'],
                ['text' => "🇷🇺 Русский", 'callback_data' => 'lang:ru'],
                ['text' => "🇺🇿 Кирилл", 'callback_data' => 'lang:kr'],
            ],
        ];

        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $text,
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    protected function processLanguage($update, $serviceClass, $nextHandlerClass, $nextState)
    {
        $callbackQuery   = $update->getCallbackQuery();
        $messageText     = $callbackQuery?->getData();
        $chatId          = $update->getMessage()?->getChat()?->getId() ?? $callbackQuery?->getMessage()?->getChat()?->getId();
        $callbackMessage = $callbackQuery?->getMessage();

        if (strpos($messageText, 'lang:') === 0) {
            if ($callbackMessage) {
                Telegram::deleteMessage([
                    'chat_id'    => $chatId,
                    'message_id' => $callbackMessage->getMessageId(),
                ]);
            }
            $lang = str_replace('lang:', '', $messageText);
            Log::info("Language selected: $lang");
            Cache::store('redis')->put("tg_lang:$chatId", $lang, now()->addDays(7));
            app($serviceClass)->mergeToCache($chatId, [
                'chat_id' => $chatId,
                'lang'    => $lang,
                'state'   => $nextState,
            ]);
            return app($nextHandlerClass)->ask($chatId);
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text'    => "❗️ Iltimos, tilni tanlang.\n❗️ Пожалуйста, выберите язык.\n❗️ Илтимос, тилни танланг.",
        ]);
    }

    public function handle($update)
    {
        return $this->processLanguage(
            $update,
            RegisterService::class,
            PhoneStepHandler::class,
            'waiting_for_phone'
        );
    }

    public function handleUpdate($update)
    {
        return $this->processLanguage(
            $update,
            UserUpdateService::class,
            NameStepHandler::class,
            'waiting_for_name'
        );
    }
}
