<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Company;
use App\Models\Platform;
use App\Models\Promotions;
use App\Models\WinnerSelectionType;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = Platform::all();

        Company::all()->each(function ($company) use ($platforms) {
            for ($i = 1; $i <= 2; $i++) {
                $promotion = Promotions::create([
                    'company_id' => $company->id,
                    'name' => [
                        'uz' => "Aksiya {$i} - " . $company->getTranslation('name', 'uz'),
                        'ru' => "Промоакция {$i} - " . $company->getTranslation('name', 'ru'),
                        'kr' => "프로모션 {$i} - " . $company->getTranslation('name', 'kr'),
                    ], // <<< qo‘shildi
                    'title' => [
                        'uz' => "Aksiya {$i} - " . $company->getTranslation('title', 'uz'),
                        'ru' => "Промоакция {$i} - " . $company->getTranslation('title', 'ru'),
                        'kr' => "프로모션 {$i} - " . $company->getTranslation('title', 'kr'),
                    ],
                    'description' => [
                        'uz' => "Bu {$i}-aksiyaning tavsifi - " . $company->getTranslation('title', 'uz'),
                        'ru' => "Описание {$i}-й акции - " . $company->getTranslation('title', 'ru'),
                        'kr' => "{$i}번째 프로모션 설명 - " . $company->getTranslation('title', 'kr'),
                    ],
                    'is_active' => (bool)random_int(0, 1),
                    'is_public' => (bool)random_int(0, 1),
                    'is_prize' => (bool)random_int(0, 1),
                    'extra_conditions' => [
                        'min_purchase' => rand(10000, 50000),
                        'region_restriction' => false,
                    ],
                    'start_date' => Carbon::now()->subDays(rand(1, 30)),
                    'end_date' => Carbon::now()->addDays(rand(15, 60)),
                    'created_by_user_id' => rand(1, 3),
                    'status' => 'draft',
                ]);

                // Attach 1–3 random platforms
                // $promotion->platforms()->attach($platforms->random(rand(1, 3))->pluck('id'));

                // Attach 1 random winner selection type

            }
        });
    }
}
