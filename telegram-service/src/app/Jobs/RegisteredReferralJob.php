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

class RegisteredReferralJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $chatId;
    protected ?string $referredId;
    protected $username;

    public function __construct(string $chatId, ?string $referredId = null, $username)
    {
        $this->chatId = $chatId;
        $this->referredId = $referredId;
        $this->username = $username;
    }

    public function handle(): void
    {
        $forwarder = app(FromServiceRequest::class);
        $translator = app(Translator::class);
        $lang = Cache::store("bot")->get("tg_lang:$this->chatId", 'uz');

        $promoball = Cache::store('bot')->remember('promo_settings_start_bot', now()->addHours(1), function () use ($forwarder) {
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
        $registerPoints = $promoball['refferal_registered_points'];
        $messageTemplate = $translator->get($this->referredId, 'referral_register_text');

        $message = str_replace(
            ['::promoball', '::username'],
            [$registerPoints, $this->username ?? 'Yangi foydalanuvchi'],
            $messageTemplate
        );


        $res = $forwarder->forward(
            'POST',
            config('services.urls.promo_service'),
            '/webapp/add-points-to-user_register',
            ['promoball' => $registerPoints, 'referred_chat_id' => $this->chatId, 'referred_id' => $this->referredId, 'username' => $this->username]
        );

        if (!$res->successful()) {
            Log::error('Userni olishda xatolik', [
                'status' => $res->status(),
                'body' => $res->body(),
            ]);
            return;
        }
        $data = $res->json();

        if (!empty($data['chat_id']) && $data['exists']) {

            try {
                Telegram::sendMessage([
                    'chat_id' => $data['chat_id'],
                    'text' => $message,
                ]);
            } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {

                $msg = $e->getMessage();

                if (str_contains($msg, 'bot was blocked by the user') || str_contains($msg, 'message is not modified')) {
                    Log::warning("Foydalanuvchi xabarni ololmadi (bloklangan yoki o'zgartirish yo'q): {$this->chatId}, msg: $msg");
                    return; // shunchaki return qilamiz
                }

                // boshqa xatoliklar bo'lsa, log qilamiz va throw qilamiz, job retry qilinadi
                Log::error("TelegramResponseException: {$msg}", ['chat_id' => $this->chatId]);
                throw $e;
            }
        }
    }
}
