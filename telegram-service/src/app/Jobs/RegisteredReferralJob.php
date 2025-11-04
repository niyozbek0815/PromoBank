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

    public function __construct(string $chatId,?string $referredId = null, $username)
    {
        $this->chatId = $chatId;
        $this->referredId = $referredId;
        $this->username = $username;
    }

    public function handle(): void
    {
        $forwarder = app(FromServiceRequest::class);
        $translator = app(Translator::class);
        $baseUrl = config('services.urls.auth_service');
        $lang = Cache::store('redis')->get("tg_lang:$this->chatId", 'uz');


        // 🔧 Asosiy yuklama
        $payload = [
            'chat_id' => $this->chatId,
            'referredId'=>$this->referredId,
            'lang' => $lang,
        ];
        $promoball = Cache::remember('promo_settings_start_bot', now()->addHours(1), function () use ($forwarder) {
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
        Log::info("RegisterRefferralJob ishga tushdi", [
            'promoball' => $registerPoints,
            'referred_id' => $this->referredId,
            'username' => $this->username,
            'lang' => $lang,
        ]);

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
            ['promoball' => $registerPoints,'referred_chat_id'=>$this->chatId, 'referred_id'=>$this->referredId, 'username'=>$this->username]
        );

        if (!$res->successful()) {
            Log::error('Userni olishda xatolik', [
                'status' => $res->status(),
                'body' => $res->body(),
            ]);
            return;
        }
        $data = $res->json();

        Log::info("resp", ['data' => $data]);
        if(!empty($data['chat_id']) && $data['exists']){
            Telegram::sendMessage([
                'chat_id' => $data['chat_id'],
                'text' => $message,
            ]);
        }

    }
}
