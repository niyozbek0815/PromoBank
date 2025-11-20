<?php

namespace App\Telegram\Handlers;

use Telegram\Bot\Laravel\Facades\Telegram;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Log;

class Subscriptions
{


    public function __construct(
        protected Translator $translator
    ) {
    }
    public function handle($chatId, $notSubscribed, $messageId = null): void
    {
        $keyboard = array_map(fn($ch) => [
            ['text' => "🔗 {$ch}", 'url' => "https://t.me/" . ltrim($ch, '@')]
        ], $notSubscribed);

        $keyboard[] = [['text' => $this->translator->get($chatId, 'check'), 'callback_data' => 'check_subscriptions']];

        $text = $this->translator->get($chatId, 'subscription_prompt');

        $replyMarkup = json_encode(['inline_keyboard' => $keyboard]);

        try {
            if (!empty($messageId)) {
                // Eski xabarni olish
                $currentMessage = null;
                try {
                    $currentMessage = Telegram::getChat(['chat_id' => $chatId])
                        ->getMessageById($messageId); // agar mavjud bo'lsa
                } catch (\Throwable $e) {
                    // Agar olishda xatolik bo'lsa davom etamiz
                }

                $currentText = $currentMessage?->getText() ?? '';
                $currentMarkup = json_encode($currentMessage?->getReplyMarkup()?->toArray() ?? []);

                // Agar matn va markup o'zgarmagan bo'lsa, edit qilinmaydi
                if ($currentText === $text && $currentMarkup === $replyMarkup) {
                    return;
                }

                // Edit xabar
                Telegram::editMessageText([
                    'chat_id' => $chatId,
                    'message_id' => (int) $messageId,
                    'text' => $text,
                    'reply_markup' => $replyMarkup,
                    'parse_mode' => 'HTML',
                ]);
                return;
            }

            // Yangi xabar yuborish
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'reply_markup' => $replyMarkup,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Throwable $e) {
            Log::error('Subscription xabarini yuborish/edit qilishda xatolik', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
