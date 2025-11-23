<?php

namespace App\Telegram\Services;
use App\Jobs\RegisterPrizeJob;
use Illuminate\Support\Facades\Cache;
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

    private function isBotUsername(string $username): bool
    {
        $u = strtolower($username);
        return str_ends_with($u, '_bot') || str_ends_with($u, 'bot');
    }


}
