<?php

namespace App\Telegram\Services;

use App\Jobs\RegisterPrizeJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Telegram\Bot\Laravel\Facades\Telegram;

class SubscriptionService
{
    /**
     * Foydalanuvchining kanal a'zoligini tekshiradi.
     * Botlarni tekshiruvdan tashlab o‘tadi, lekin agar obuna bo‘lmagan kanal topilsa,
     * ularni oxirida doimiy ro‘yxatga qo‘shadi.
     */

    public function handle()
    {

    }
    public function checkUserSubscriptions(int|string $chatId): array
    {
        // 🔹 Avval cache'ni tekshiramiz
        $cacheKey = "tg_subscriptions_ok:$chatId";
        // Cache::connection('bot')->forget($cacheKey);

        if (Cache::connection('bot')->has($cacheKey)) {
            Log::info("{$chatId} uchun obuna cache mavjud — qayta tekshirilmaydi.");
            return []; // ✅ Barcha kanallarga obuna deb hisoblanadi
        }

        $channels = [
            '@my5tv',
            '@promobank_uz',
            '@musofir_shou'
        ];

        $notSubscribed = [];
        $bots = [];

        foreach ($channels as $channel) {
            // 🔹 Agar bot bo‘lsa, tekshiruvdan o‘tkazmaymiz
            if ($this->isBotUsername($channel)) {
                $bots[] = $channel;
                Log::info("{$channel} bot ekan, tekshiruv tashlab o'tildi.");
                continue;
            }

            try {
                $member = Telegram::getChatMember([
                    'chat_id' => $channel,
                    'user_id' => $chatId,
                ]);

                $status = $member->status ?? null;
                Log::info("Channel {$channel} -> status", ['status' => $status]);

                if (!in_array($status, ['creator', 'administrator', 'member'])) {
                    $notSubscribed[] = $channel;
                }
            } catch (\Throwable $e) {
                Log::error("Channel tekshirishda xatolik: {$channel}", [
                    'error' => $e->getMessage(),
                ]);

                if (!str_contains($e->getMessage(), 'bot is not a member')) {
                    $notSubscribed[] = $channel;
                }
            }
        }

        // 🔹 Agar obuna bo‘lmagan kanal bo‘lsa — botlarni ham qo‘shamiz
        if (!empty($notSubscribed) && !empty($bots)) {
            $notSubscribed = array_merge($notSubscribed, $bots);
        }

        // ✅ Agar hammasiga obuna bo‘lsa — cache saqlaymiz (masalan 1 soat)
        if (empty($notSubscribed)) {
            Queue::connection('rabbitmq')->push(new RegisterPrizeJob(
                chatId: $chatId,
            ));
            Cache::connection('bot')->put($cacheKey, true, now()->addHour(3));
            Log::info("{$chatId} barcha kanallarga obuna — cache saqlandi (1 soatga).");
        }

        return $notSubscribed;
    }

    public function deleteMessage($chatId, $messageId = null)
    {

        if ($messageId) {
            try {
                Telegram::deleteMessage([
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                ]);
            } catch (\Throwable $e) {
                Log::warning("Tekshiruv xabarini o'chirishda xatolik", [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
    /**
     * Foydalanuvchiga obuna bo‘lish uchun xabar yuboradi yoki eski xabarni yangilaydi.
     */

    /**
     * Username botligini tekshiradi (masalan: @PromoBank_uz_bot, @TestBot)
     */
    private function isBotUsername(string $username): bool
    {
        $u = strtolower($username);
        return str_ends_with($u, '_bot') || str_ends_with($u, 'bot');
    }

    public function storePendingAction($chatId, $update, array $payload = [])
    {
        Cache::connection('bot')->set("tg_pending_action:$chatId", json_encode($update), 600);
    }

    public function getPendingAction($chatId): ?array
    {

        $key = "tg_pending_action:$chatId";

        // Cache’dan olish
        $json = Cache::connection('bot')->get($key);

        if ($json) {
            // O'qilganidan so'ng darhol o'chirish
            Cache::connection('bot')->forget($key);
            return json_decode($json, true);
        }

        return null;
    }

    public function isSubscriptionCached(int|string $chatId): bool
    {
        return Cache::connection('bot')->has("tg_subscriptions_ok:$chatId");
    }

}
