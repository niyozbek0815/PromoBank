<?php

namespace App\Jobs;

use App\Services\FromServiceRequest;
use App\Telegram\Services\Translator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartAndRefferralJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $chatId;
    protected ?string $referrerId, $username;

    public function __construct(string $chatId, $username, ?string $referrerId = null)
    {
        $this->chatId = $chatId;
        $this->referrerId = $referrerId;
        $this->username = $username;

    }

    public function handle(): void
    {
        $forwarder = app(FromServiceRequest::class);
        $translator = app(Translator::class);
        $baseUrl = config('services.urls.auth_service');
        $lang = Cache::connection('bot')->get("tg_lang:$this->chatId", 'uz');

        Log::info("StartAndRefferralJob ishga tushdi", [
            'chat_id' => $this->chatId,
            'referrer_id' => $this->referrerId,
            'lang' => $lang,
        ]);

        // 🔒 O‘zi o‘zini referal qilgan holat
        if (!empty($this->referrerId) && $this->chatId === $this->referrerId) {
            Log::warning("Referral ID chat ID bilan bir xil. Referral null qilindi.", [
                'chat_id' => $this->chatId,
                'referrer_id' => $this->referrerId,
            ]);

            $this->referrerId = null;
        }

        // 🔧 Asosiy yuklama
        $payload = [
            'chat_id' => $this->chatId,
            'lang' => $lang,
        ];

        if (!empty($this->referrerId)) {
            $payload['referrer_id'] = $this->referrerId;
        }

        // 🚀 Auth-servisga yuborish
        $response = $forwarder->forward('POST', $baseUrl, '/bot_start', $payload);

        if (!$response->successful()) {
            Log::error('Userni olishda xatolik', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return;
        }

        $data = $response->json();
        Log::info("Bot start javobi", ['data' => $data]);
        $new = $data['new_user'] ?? false;
        $referredUser = $data['user'];
        $referralExists = $data['referral_exists'] ?? false;
        $referrerUser = $data['referrer_user'] ?? null;
        Log::info("StartAndRefferralJob foydalanuvchi holati", [
            'new_user' => $new,
            'referral_exists' => $referralExists,
            'referrer_user' => $referrerUser,
        ]);
        // 🎯 Yangi foydalanuvchi + mavjud referal
        if ($new && $referralExists && !empty($this->referrerId)) {
            $promoball = Cache::connection('bot')->remember('promo_settings_start_bot', now()->addHours(1), function () use ($forwarder) {
                $response = $forwarder->forward(
                    'GET',
                    config('services.urls.promo_service'),
                    '/webapp/platform-promo-settings',
                    []
                );
                if ($response->successful()) {
                    return $response->json() ?? [
                        'refferal_start_points' => 1,
                        'refferal_registered_points' => 2,
                    ];
                }
                return [
                    'refferal_start_points' => 1,
                    'refferal_registered_points' => 2,
                ];
            });
            $startPoints = $promoball['refferal_start_points'];
            $messageTemplate = $translator->get($this->referrerId, 'referral_start_text');

            $message = str_replace(
                ['::promoball', '::username'],
                [$startPoints, $this->username ?? 'Yangi foydalanuvchi'],
                $messageTemplate
            );

            Telegram::sendMessage([
                'chat_id' => $this->referrerId,
                'text' => $message,
            ]);
            $res = $forwarder->forward(
                'POST',
                config('services.urls.promo_service'),
                '/webapp/add-points-to-user',
                ['promoball' => $startPoints, 'referrer_id' => $referrerUser['id'], 'referred_id' => $referredUser['id'], 'referred_chat_id' => $this->chatId, 'referrer_chat_id' => $this->referrerId, 'referred_username' => $this->username]
            );
        }
    }
}