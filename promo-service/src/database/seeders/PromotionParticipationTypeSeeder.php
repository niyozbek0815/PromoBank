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

        foreach ($promotions as $promotion) {
            // Agar short_number turi mavjud bo'lsa, faqat uni tanlaymiz
            $shortNumberType = $participationTypes->firstWhere('slug', 'short_number');

            if ($shortNumberType) {
                $types = [$shortNumberType->id]; // faqat short_number
            } else {
                // Aks holda 2–3 tasini tasodifiy tanlaymiz
                $types = Arr::random($participationTypes->pluck('id')->toArray(), rand(2, 3));
            }

            foreach ($types as $typeId) {
                $type = $participationTypes->firstWhere('id', $typeId);

                // Default additional_rules
                $rules = ['limit' => rand(1, 5)];

                if ($type->slug === 'sms') {
                    $rules = ['phone' => '1112'];
                } elseif ($type->slug === 'short_number') {
                    $rules = ['short_number_seconds' => rand(10, 60)]; // misol uchun sekund
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
