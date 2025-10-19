<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key_name' => 'hero_title',
                'val' => json_encode([
                    'uz' => 'Har xaridda imkoniyat: o‘yna, yut, quvon',
                    'ru' => 'Каждая покупка – это шанс: играй, выигрывай, радуйся',
                    'kr' => 'Ҳар харидда имконият: ўйна, ют, қувон',
                    'en' => 'Every purchase is a chance: play, win, enjoy',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 1,
            ],
            [
                'key_name' => 'footer_logo',
                'val' => json_encode('assets/image/hero/logo.svg', JSON_UNESCAPED_UNICODE),
                'status' => 1,
            ],
            [
                'key_name' => 'navbar_logo',
                'val' => json_encode('assets/image/hero/logo.svg', JSON_UNESCAPED_UNICODE),
                'status' => 1,
            ],
            [
                'key_name' => 'footer_description',
                'val' => json_encode([
                    'uz' => 'Har xaridda imkoniyat: o‘yna, yut, quvon',
                    'ru' => 'Каждая покупка – это шанс: играй, выигрывай, радуйся',
                    'kr' => 'Ҳар харидда имконият: ўйна, ют, қувон',
                    'en' => 'Every purchase is a chance: play, win, enjoy',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 1,
            ],
            [
                'key_name' => 'footer_bottom',
                'val' => json_encode([
                    'uz' => ' PromoBank. Barcha huquqlar himoyalangan.',
                    'ru' => ' PromoBank. Все права защищены.',
                    'kr' => ' PromoBank. Барча ҳуқуқлар ҳимояланган.',
                    'en' => ' PromoBank. All rights reserved.',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 1,
            ],
            [
                'key_name' => 'languages',
                'val' => json_encode([
                    'available' => ['uz', 'ru', 'kr', 'en'],
                    'default' => 'uz',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 1,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key_name' => $setting['key_name']],
                [
                    'val' => $setting['val'],
                    'status' => $setting['status'],
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );
        }
    }
}
