<?php

namespace Database\Seeders;

use App\Models\PromotionMessage;
use App\Models\Promotions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromotionMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ Default messages yaratamiz (prize_id = null)
        $platforms = ['sms', 'mobile', 'bot', 'all'];
        $types = ['success', 'fail','claim', 'info', 'etc'];

        // foreach ($platforms as $platform) {
        //     foreach ($types as $type) {
        //         PromotionMessage::firstOrCreate([
        //             'prize_id' => null,
        //             'platform' => $platform,
        //             'message_type' => $type,
        //         ], [
        //             'message' => [
        //                 'uz' => "Default {$platform} {$type} (uz)",
        //                 'ru' => "Default {$platform} {$type} (ru)",
        //                 'kr' => "Default {$platform} {$type} (kr)",
        //             ]
        //         ]);
        //     }
        // }

        // ✅ Har bir Prize uchun bitta custom message misol uchun
        Promotions::all()->each(function ($promotion, $index) {
            // if ($index % 3 !== 0) { // 0, 3, 6,... <- SKIP qilish
            PromotionMessage::firstOrCreate([
                'promotion_id' => $promotion->id,
                'platform' => 'mobile',
                'message_type' => 'success',
            ], [
                'message' => [
                    'uz' => "Promocode muvaffaqiyatli ro'yhatga olindi.",
                    'ru' => "Промокод успешно зарегистрирован.",
                    'kr' => "프로모코드가 성공적으로 등록되었습니다.",
                ]
            ]);
            // }
        });
    }
}