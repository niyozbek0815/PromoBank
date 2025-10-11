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
                $type = $participationTypes->firstWhere('id', $typeId);

                $data = [
                    'promotion_id' => $promotion->id,
                    'participation_type_id' => $typeId,
                    'is_enabled' => true,
                    'additional_rules' => json_encode(['limit' => rand(1, 5)]),
                ];

                // Agar participation turi 'sms' bo‘lsa, qo‘shimcha phone field qo‘shamiz
                if (strtolower($type->slug ?? '') === 'sms') {
                    $data['additional_rules'] = json_encode(['phone' => '1112']);
                } else {
                    $data['additional_rules'] = json_encode(['limit' => rand(1, 5)]);
                }

                PromotionParticipationType::create($data);
            }
        }
    }
}
