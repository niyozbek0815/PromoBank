<?php

namespace Database\Seeders;

use App\Models\Prize;
use App\Models\PrizeMessage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrizeMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ Default messages yaratamiz (prize_id = null)
        $platforms = ['sms', 'mobile', 'bot', 'all'];
        $types = ['success', 'fail', 'info', 'etc'];

        // foreach ($platforms as $platform) {
        //     foreach ($types as $type) {
        //         PrizeMessage::firstOrCreate([
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
        Prize::all()->each(function ($prize, $index) {
            if ($index % 3 !== 0) { // 0, 3, 6,... <- SKIP qilish
                PrizeMessage::firstOrCreate([
                    'prize_id' => $prize->id,
                    'platform' => 'mobile',
                    'message_type' => 'success',
                ], [
                    'message' => [
                        'uz' => "{$prize->name} uchun YUTDI (uz)",
                        'ru' => "{$prize->name} Вы выиграли (ru)",
                        'kr' => "{$prize->name} 당첨 (kr)",
                    ]
                ]);
            }
        });
    }
}
