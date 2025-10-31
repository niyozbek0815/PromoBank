<?php

namespace App\Jobs;

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
        // 🔹 Dinamik promo kod (hozircha misol uchun)
        $promoCode = 'SHJDBSDBJHSBH1234567';
Log::info('🎥 RegisterPrizeJob ishga tushdi', ['chat_id' => $this->chatId, 'promo_code' => $promoCode]);
        // 🔹 Matnni o‘zgaruvchida saqlaymiz (HTML formatda)
        $text = <<<HTML
<b>🎉 PromoBank'ga xush kelibsiz!</b>

Siz birinchi marta bizga qo‘shildingiz 🎊
Shu munosabat bilan biz sizga <b>ONTV</b> platformasida foydalanish uchun <b>bepul PROMOKOD</b> taqdim etamiz:

🎁 <code>{$promoCode}</code>

📲 <b>Batafsil ma'lumot uchun:</b>
👉 <a href="https://ontv.uz">ONTV platformasiga o'tish</a>
👉 <a href="https://t.me/musofir_shou">Telegram kanalimizi kuzating</a>
👉 <a href="https://promobank.uz">Promobank rasmiy sayti</a>

<b>🎬 Har kuni yangi imkoniyatlar sizni kutmoqda!</b>
HTML;

        // 🔹 Video bilan birga caption jo‘natish
        Telegram::sendVideo([
            'chat_id' => $this->chatId,
            'video' => InputFile::create('https://qadarun.com/namuna/video6.mp4'),
            'caption' => $text,
            'parse_mode' => 'HTML',
        ]);

        Log::info('🎥 RegisterPrizeJob video xabari yuborildi', ['chat_id' => $this->chatId]);
    }
}
