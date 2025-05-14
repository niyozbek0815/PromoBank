<?php

namespace Database\Seeders;

use App\Models\ParticipationType;
use App\Models\PromotionParticipationType;
use App\Models\Promotions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class PromotionParticipationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Barcha promotionlarni olish
        $promotions = Promotions::all();

        // Barcha participation turlarini olish
        $participationTypes = ParticipationType::all();

        // Har bir promotion uchun 2 yoki 3 ta tur qo'shish
        foreach ($promotions as $promotion) {
            // Tasodifiy 2 yoki 3 tadan turni tanlaymiz
            $types = Arr::random($participationTypes->pluck('id')->toArray(), rand(2, 3));

            foreach ($types as $typeId) {
                PromotionParticipationType::create([
                    'promotion_id' => $promotion->id,
                    'participation_type_id' => $typeId,
                    'is_enabled' => true, // yoki kerakli holatda
                    'additional_rules' => json_encode(['limit' => rand(1, 5)]), // misol uchun qo‘shimcha qoidalar
                ]);
            }
        }
    }
}
