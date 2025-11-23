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
    public function check(int|string $chatId, bool $dispatchRegisterPrize = false): array
    {
        // $this->forget($chatId);

        // Agar avvaldan tekshiruvdan o'tgan bo‘lsa
        if ($this->exists($chatId)) {
            return [];
        }
        // $channels = [
        //     '@niyozbek_mn',
        //     '@classic_mc',
        //     // '@promobank_uz',
        // ];


        $channels = [
            '@my5tv',
            '@promobank_uz',
            '@musofir_shou',
        ];
        $notSubscribed = [];
        $bots = [];

        foreach ($channels as $channel) {

            if ($this->isBotUsername($channel)) {
                $bots[] = $channel;
                continue;
            }

            try {
                $member = Telegram::getChatMember([
                    'chat_id' => $channel,
                    'user_id' => $chatId,
                ]);

                $status = $member->status ?? null;

                if (!in_array($status, ['creator', 'administrator', 'member'], true)) {
                    $notSubscribed[] = $channel;
                }

            } catch (\Throwable $e) {
                if (!str_contains($e->getMessage(), 'bot is not a member')) {
                    $notSubscribed[] = $channel;
                }
            }
        }

        // Agar botlar bo‘lsa va unsubscribed bo‘lsa — birlashtiramiz
        if ($notSubscribed && $bots) {
            $notSubscribed = array_merge($notSubscribed, $bots);
        }

        // To‘liq subscribe qilingan bo‘lsa
        if (!$notSubscribed) {
            $this->put($chatId);

            if ($dispatchRegisterPrize) {
                Log::info('Segisterda dispatch qildim ');
                Queue::connection('rabbitmq')
                    ->push(new RegisterPrizeJob($chatId));
            }
        }

        return $notSubscribed;
    }
    public function put($chatId)
    {
        Cache::store('bot')->put("tg_subs:$chatId", true, 103800);
    }
    public function forget($chatId)
    {
        Cache::store('bot')->forget("tg_subs:$chatId");
    }
    public function exists($chatId): bool
    {
        return Cache::store('bot')->has("tg_subs:$chatId");
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


    private function isBotUsername(string $username): bool
    {
        $u = strtolower($username);
        return str_ends_with($u, '_bot') || str_ends_with($u, 'bot');
    }

    public function storePendingAction($chatId, $update, array $payload = [])
    {
        Cache::store('bot')->set("tg_pending_action:$chatId", json_encode($update), 600);
    }

    public function getPendingAction($chatId): ?array
    {

        $key = "tg_pending_action:$chatId";
        $json = Cache::store('bot')->get($key);

        if ($json) {
            Cache::store('bot')->forget($key);
            return json_decode($json, true);
        }

        return null;
    }



}