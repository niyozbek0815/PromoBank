<?php

namespace App\Jobs;

use App\Services\FromServiceRequest;
use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StartAndRefferralJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    protected string $chatId;
    protected ?string $username;
    protected array $referrerUser;
    protected array $me;

    public function __construct(string $chatId, ?string $username, array $referrerUser, array $me)
    {
        $this->chatId = $chatId;
        $this->username = $username;
        $this->referrerUser = $referrerUser;
        $this->me = $me;
    }

    public function handle(): void
    {
        try {
            $forwarder = app(FromServiceRequest::class);
            $sender = app(SendMessages::class);
            $translator = app(Translator::class);

            $referrerUser = $this->referrerUser;
            $me = $this->me;

            // Promo settings ni cache orqali olish
            $promoball = Cache::store('bot')->remember('promo_settings_start_bot', now()->addHours(1), function () use ($forwarder) {
                $resp = $forwarder->forward('GET', config('services.urls.promo_service'), '/webapp/platform-promo-settings', []);
                return $resp->successful() ? $resp->json() : ['refferal_start_points' => 1, 'refferal_registered_points' => 2];
            });

            // Referral xabar tayyorlash
            $message = str_replace(
                ['::promoball', '::username'],
                [$promoball['refferal_start_points'], $this->username],
                $translator->get($referrerUser['chat_id'], 'referral_start_text')
            );

            // Xabar yuborish
            $sender->handle([
                'chat_id' => $referrerUser['chat_id'],
                'text' => $message,
            ]);

            // Promo ball qo‘shish
            $forwarder->forward('POST', config('services.urls.promo_service'), '/webapp/add-points-to-user', [
                'promoball' => $promoball['refferal_start_points'],
                'referrer_id' => $referrerUser['id'],
                'referred_id' => $me['id'],
                'referred_chat_id' => $this->chatId,
                'referrer_chat_id' => $referrerUser['chat_id'],
                'referred_username' => $this->username
            ]);

        } catch (\Throwable $e) {
            Log::error('StartAndRefferralJob xatolik yuz berdi', [
                'chat_id' => $this->chatId,
                'username' => $this->username,
                'referrer_id' => $this->referrerUser['id'] ?? null,
                'me_id' => $this->me['id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
