<?php

namespace App\Jobs;

use App\Services\FromServiceRequest;
use App\Telegram\Services\Translator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;use Illuminate\Foundation\Queue\Queueable;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Exceptions\TelegramResponseException;

class StartAndRefferralJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3; // maksimal retry soni
    public int $timeout = 30; // maksimal ishlash vaqti

    protected string $chatId;
    protected ?string $referrerId, $username;

    public function __construct(string $chatId, string $username, ?string $referrerId = null)
    {
        $this->chatId = $chatId;
        $this->username = $username;
        $this->referrerId = $referrerId;
    }

    public function handle(): void
    {
        $forwarder = app(FromServiceRequest::class);
        $translator = app(Translator::class);
        $lang = Cache::store('bot')->get("tg_lang:$this->chatId", 'uz');

        Log::info("2StartAndRefferralJob ishga tushdi", [
            'chat_id' => $this->chatId,
            'referrer_id' => $this->referrerId,
            'lang' => $lang,
        ]);

        if ($this->referrerId === $this->chatId) {
            Log::warning("Referral ID chat ID bilan bir xil. Referral null qilindi.", [
                'chat_id' => $this->chatId,
            ]);
            $this->referrerId = null;
        }

        $payload = ['chat_id' => $this->chatId, 'lang' => $lang];
        if ($this->referrerId) $payload['referrer_id'] = $this->referrerId;

        $response = $forwarder->forward('POST', config('services.urls.auth_service'), '/bot_start', $payload);
        if (!$response->successful()) {
            Log::error('Userni olishda xatolik', ['status' => $response->status(), 'body' => $response->body()]);
            return;
        }

        $data = $response->json();
        $new = $data['new_user'] ?? false;
        $referredUser = $data['user'];
        $referralExists = $data['referral_exists'] ?? false;
        $referrerUser = $data['referrer_user'] ?? null;

        if ($new && $referralExists && $this->referrerId) {
            $promoball = Cache::store('bot')->remember('promo_settings_start_bot', now()->addHours(1), function () use ($forwarder) {
                $resp = $forwarder->forward('GET', config('services.urls.promo_service'), '/webapp/platform-promo-settings', []);
                return $resp->successful() ? $resp->json() : ['refferal_start_points' => 1, 'refferal_registered_points' => 2];
            });

            $message = str_replace(
                ['::promoball', '::username'],
                [$promoball['refferal_start_points'], $this->username],
                $translator->get($this->referrerId, 'referral_start_text')
            );

        try {
    Telegram::sendMessage([
        'chat_id' => $this->referrerId,
        'text' => $message,
    ]);
} catch (TelegramResponseException $e) {
    $msg = $e->getMessage();
    if (str_contains($msg, 'bot was blocked by the user') || str_contains($msg, 'message is not modified')) {
        Log::warning("Telegram xatolik, job bekor qilindi: $msg", ['chat_id' => $this->referrerId]);
        return; // Job retry qilinmaydis
    }
    throw $e; // boshqa xatoliklarda retry qilinsin
}

            // Promoball yuborish
            $forwarder->forward('POST', config('services.urls.promo_service'), '/webapp/add-points-to-user', [
                'promoball' => $promoball['refferal_start_points'],
                'referrer_id' => $referrerUser['id'],
                'referred_id' => $referredUser['id'],
                'referred_chat_id' => $this->chatId,
                'referrer_chat_id' => $this->referrerId,
                'referred_username' => $this->username
            ]);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("StartAndRefferralJob failed", [
            'chat_id' => $this->chatId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
