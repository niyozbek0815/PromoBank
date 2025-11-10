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
        $promotions = Promotions::all();
        $participationTypes = ParticipationType::all();

        $secretNumberType = $participationTypes->firstWhere('slug', 'secret_number');

        foreach ($promotions as $promotion) {
            $types = [];

            // 1/4 ehtimol bilan secret_number qo'shish
            if ($secretNumberType && rand(1, 4) === 1) { // 1 dan 4 gacha tasodifiy son, 1 bo'lsa qo'shadi
                $types[] = $secretNumberType->id;
            }

            // Qolgan ishtirok turlarini tasodifiy tanlash
            $otherTypes = $participationTypes
                ->where('slug', '!=', 'secret_number')
                ->pluck('id')
                ->toArray();

            // Tasodifiy 1-2 ta boshqa turni tanlaymiz
            $types = array_merge($types, Arr::random($otherTypes, rand(1, min(2, count($otherTypes)))));

            foreach ($types as $typeId) {
                $type = $participationTypes->firstWhere('id', $typeId);

                // Default additional_rules
                $rules = ['limit' => rand(1, 5)];

                if ($type->slug === 'sms') {
                    $rules = ['phone' => '1112'];
                } elseif ($type->slug === 'secret_number') {
                    $rules = ['secret_number_seconds' => rand(10, 60)]; // misol uchun sekund
                }

                PromotionParticipationType::create([
                    'promotion_id' => $promotion->id,
                    'participation_type_id' => $typeId,
                    'is_enabled' => true,
                    'additional_rules' => json_encode($rules),
                ]);
            }
        }
    }
}
