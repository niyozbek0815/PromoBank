<?php

namespace App\Jobs;

use App\Services\FromServiceRequest;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserSessionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RegisterPrizeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $chatId;

    /**
     * Yangi job yaratish
     */
    public function __construct(string $chatId)
    {
        $this->chatId = $chatId;
    }

    /**
     * Job’ni bajarish
     */
    public function handle(): void
    {
        $forwarder = app(FromServiceRequest::class);
        $translator = app(Translator::class);
        $user = app(UserSessionService::class)->get($this->chatId);
        $userId = $user['id'] ?? null;
        $baseUrl = config('services.urls.promo_service'); // misol: http://promo_nginx
        $route = '/telegram/ontv/ontv_vaucher'; // full route
        $response = $forwarder->forward('POST', $baseUrl, $route, ['user_id' => $userId]);
        if (!$response->successful()) {
            Log::error('Userni olishda xatolik', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return;
        }

        $data = $response->json();
        Log::info("return Data", ['data' => $data]);
        if ($data['is_new']) {


            // 🔹 Dinamik promo kod (hozircha misol uchun)
            $promoCode = $data['code'];
            $video_url = $data['url'] ?? 'https://promobank.io/namuna/video6.mp4';
            Log::info('🎥 RegisterPrizeJob ishga tushdi', ['chat_id' => $this->chatId, 'promo_code' => $promoCode]);
            // 🔹 Matnni o‘zgaruvchida saqlaymiz (HTML formatda)
            $translator = app(Translator::class);
            $textTemplate = $translator->get($this->chatId, 'ontv_text');

            // ::promoCode ni o‘rnatish
            $text = str_replace('::promoCode', $promoCode, $textTemplate);

            // 🔹 Video bilan birga caption jo‘natish

            try {
                Telegram::sendPhoto([
                    'chat_id' => $this->chatId,
                    // 'video' => InputFile::create($video_url),
                    'photo' => InputFile::create($video_url),  // Bu yerda video emas, rasm
                    'caption' => $text,
                    'parse_mode' => 'HTML',
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
            Log::info('🎥 RegisterPrizeJob video xabari yuborildi', ['chat_id' => $this->chatId]);
        }



    }
}
