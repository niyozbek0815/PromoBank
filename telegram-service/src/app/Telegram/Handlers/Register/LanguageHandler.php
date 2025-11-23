<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;
use Illuminate\Support\Facades\Cache;

class LanguageHandler
{
    public function __construct(
        protected Translator $translator,
        protected SendMessages $sender
    ) {
    }

    public function ask($chatId)
    {
        // 🔹 4 tildagi "til tanlang" matnlarini Translator orqali olish
        $text = implode("\n", [
            $this->translator->getForLang('language_prompt', 'uz'),
            $this->translator->getForLang('language_prompt', 'ru'),
            $this->translator->getForLang('language_prompt', 'kr'),
            $this->translator->getForLang('language_prompt', 'en'),
        ]);

        $keyboard = [
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
        ];
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    protected function processLanguage($update, $serviceClass, $nextHandlerClass, $nextState)
    {
        $callbackQuery = $update->getCallbackQuery();
        $messageText = $callbackQuery?->getData();
        $chatId = $update->getMessage()?->getChat()?->getId() ?? $callbackQuery?->getMessage()?->getChat()?->getId();
        $callbackMessage = $callbackQuery?->getMessage();

        if (strpos($messageText, 'lang:') === 0) {
            if ($callbackMessage) {
                $this->sender->delete([
                    'chat_id' => $chatId,
                    'message_id' => $callbackMessage->getMessageId(),
                ]);

            }
            $lang = str_replace('lang:', '', $messageText);
            Cache::store('bot')->put("tg_lang:$chatId", $lang, now()->addDays(8));
            app($serviceClass)->mergeToCache($chatId, [
                'chat_id' => $chatId,
                'lang' => $lang,
                'state' => $nextState,
            ]);
            return app($nextHandlerClass)->ask($chatId);
        }
        $text = implode("\n", [
            $this->translator->getForLang('language_prompt', 'uz'),
            $this->translator->getForLang('language_prompt', 'ru'),
            $this->translator->getForLang('language_prompt', 'kr'),
            $this->translator->getForLang('language_prompt', 'en'),
        ]);
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $text,
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
