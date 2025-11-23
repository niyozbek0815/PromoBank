<?php
namespace App\Telegram\Services;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendMessages
{
    public function handle(array $data): void
    {
        try {
            Telegram::sendMessage($data);
        } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {

            $msg = $e->getMessage();
            $chatId = $data['chat_id'] ?? null;

            // Foydalanuvchi botni bloklagan yoki xabar o'zgarmagan holatlari
            if (
                str_contains($msg, 'bot was blocked by the user')
                || str_contains($msg, 'message is not modified')
            ) {

                Log::warning("Telegram xabar yuborilmadi: chat_id={$chatId}, sabab={$msg}");
                return;
            }

            // Boshqa telegram xatoliklari
            Log::error("Telegram xabari yuborishda xatolik: chat_id={$chatId}, error={$msg}");

            return;
        }
    }
    public function delete(array $data): void
    {
        try {
            Telegram::deleteMessage($data);
        } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {

            $msg = $e->getMessage();
            $chatId = $data['chat_id'] ?? null;

            // Foydalanuvchi botni bloklagan yoki xabar o'zgarmagan holatlari
            if (
                str_contains($msg, 'bot was blocked by the user')
                || str_contains($msg, 'message is not modified')
            ) {

                Log::warning("Telegram xabar yuborilmadi: chat_id={$chatId}, sabab={$msg}");
                return;
            }

            // Boshqa telegram xatoliklari
            Log::error("Telegram xabari yuborishda xatolik: chat_id={$chatId}, error={$msg}");

            return;
        }
    }
}
