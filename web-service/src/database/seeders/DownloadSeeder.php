<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Download;
use App\Models\DownloadLink;

class DownloadSeeder extends Seeder
{
    public function run(): void
    {
        // Asosiy download ma'lumotlari
        $downloadData = [
            'subtitle' => [
                'uz' => 'Yuklab olish va kuzatish',          // O‘zbek (lotin)
                'ru' => 'Скачайте и следите',                // Русский
                'kr' => 'Юклаб олиш ва кузатиш',            // Ўзбек (крилл)
                'en' => 'Download and track',               // Inglizcha
            ],
            'title' => [
                'uz' => 'PromoBank bilan tez va oson yutib oling!',
                'ru' => 'Выигрывайте легко и быстро с PromoBank!',
                'kr' => 'PromoBank билан тез ва осон ютиб олинг!',
                'en' => 'Win quickly and easily with PromoBank!', // Inglizcha
            ],
            'description' => [
                'uz' => 'PromoBank mobil ilovasi va Telegram bot orqali barcha aksiyalarda qatnashing, yutuqlarni kuzating va kodlaringizni saqlang. Hoziroq yuklab oling!',
                'ru' => 'Участвуйте во всех акциях через мобильное приложение PromoBank и Telegram-бот, следите за выигрышами и сохраняйте коды. Скачайте прямо сейчас!',
                'kr' => 'PromoBank мобил иловаси ва Telegram бот орқали барча акцияларда қатнашинг, ютуқларни кузатинг ва кодларингизни сақланг. Ҳозироқ юклаб олинг!',
                'en' => 'Participate in all promotions via the PromoBank mobile app and Telegram bot, track your winnings, and save your codes. Download now!', // Inglizcha
            ],
            'image' => 'assets/image/download/intro-mobile.png',
            'status' => 1,
        ];

        // 🔄 Agar oldin mavjud bo'lsa - update, bo'lmasa create
        $download = Download::updateOrCreate(
            ['title->uz' => $downloadData['title']['uz']], // unique check
            $downloadData
        );

        // Linklar ro'yxati
        $links = [
            [
                'type' => 'googleplay',
                'url' => 'https://play.google.com/store',
                'label' => [
                    'uz' => 'Google Play',
                    'ru' => 'Google Play',
                    'kr' => 'Google Play',
                    'en' => 'Google Play',
                ],
                'position' => 1,
                'status' => 1,
            ],
            [
                'type' => 'appstore',
                'url' => 'https://apps.apple.com/',
                'label' => [
                    'uz' => 'App Store',
                    'ru' => 'App Store',
                    'kr' => 'App Store',
                    'en' => 'App Store',
                ],
                'position' => 2,
                'status' => 1,
            ],
            [
                'type' => 'telegram',
                'url' => 'https://t.me/your_promobank_bot',
                'label' => [
                    'uz' => 'Telegram',
                    'ru' => 'Telegram',
                    'kr' => 'Telegram',
                    'en' => 'Telegram',
                ],
                'position' => 3,
                'status' => 1,
            ],
        ];

        foreach ($links as $link) {
            DownloadLink::updateOrCreate(
                [
                    'download_id' => $download->id,
                    'type' => $link['type'],
                ],
                $link + ['download_id' => $download->id]
            );
        }
    }
}
