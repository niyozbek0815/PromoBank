<?php
namespace App\Telegram\Handlers;

use App\Telegram\Services\Translator;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;
use Illuminate\Support\Facades\Log;

class MainBack
{
    public function __construct(protected Translator $translator)
    {
    }

    public function handle(Update $update)
    {
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();

        $text = $this->translator->get($chatId, 'main_menu_title');

        $replyMarkup = json_encode([
            'inline_keyboard' => [
                [
                    [
                        'text' => $this->translator->get($chatId, 'menu_promotions'),
                        'web_app' => ['url' => 'https://promobank.io/webapp/promotions/1'],
                    ],
                ],
                [
                    ['text' => $this->translator->get($chatId, 'menu_social'), 'callback_data' => 'menu_social'],
                ],
                [
                    ['text' => $this->translator->get($chatId, 'menu_referral'), 'callback_data' => 'menu_referral'],
                ],
                [
                    ['text' => $this->translator->get($chatId, 'menu_profile'), 'callback_data' => 'menu_profile'],
                ],
            ],
        ]);

        // Telegram API xatosini oldini olish: faqat o'zgargan bo'lsa edit qilamiz
        try {
            $currentMessage = $update->getCallbackQuery()?->getMessage();
            $currentText = $currentMessage?->getText() ?? '';
            $currentMarkup = json_encode($currentMessage?->getReplyMarkup()?->toArray() ?? []);

            if ($currentText === $text && $currentMarkup === $replyMarkup) {
                return; // Hech qanday o'zgarish yo'q, edit qilinmaydi
            }
        } catch (\Throwable $e) {
            Log::warning("MainBack: current message olishda xatolik: {$e->getMessage()}", ['chat_id' => $chatId]);
        }

        try {
            Telegram::editMessageText([
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => $replyMarkup,
            ]);
        } catch (\Throwable $e) {
            // Xatolik bo‘lsa log qilamiz, bot to‘xtamaydi
            Log::warning("MainBack editMessageText xatolik: {$e->getMessage()}", ['chat_id' => $chatId]);
        }
    }
}
