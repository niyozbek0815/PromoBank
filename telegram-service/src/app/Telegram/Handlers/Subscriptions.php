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

        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'HTML',
        ];

        try {
            if (!empty($messageId)) {
                // Eski xabarni yangilash — editMessageText ishlashi uchun messageId integer bo'lishi kerak
                Telegram::editMessageText(array_merge($params, ['message_id' => (int) $messageId]));
                return;
            }

            // Yangi xabar yuborish
            $sent = Telegram::sendMessage($params);
        } catch (\Throwable $e) {
            Log::error('Subscription xabarini yuborish/yangi qilishda xatolik', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
