<?php
namespace Database\Seeders;

use App\Models\Company;
use App\Models\Platform;
use App\Models\Promotions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

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
                        'en' => "Promotion {$i} - " . $company->getTranslation('name', 'en'),
                        'kr' => "Promosyon {$i} - " . $company->getTranslation('name', 'kr'),
                    ],
                    'title' => [
                        'uz' => "Aksiya {$i} - " . $company->getTranslation('title', 'uz'),
                        'ru' => "Промоакция {$i} - " . $company->getTranslation('title', 'ru'),
                        'en' => "Promotion {$i} - " . $company->getTranslation('title', 'en'),
                        'kr' => "Promosyon {$i} - " . $company->getTranslation('title', 'kr'),
                    ],
                    'description' => [
                        'uz' => "Bu {$i}-aksiyaning tavsifi - " . $company->getTranslation('title', 'uz'),
                        'ru' => "Описание {$i}-й акции - " . $company->getTranslation('title', 'ru'),
                        'en' => "Description of promotion {$i} - " . $company->getTranslation('title', 'en'),
                        'kr' => "Dis krio description fo promo {$i} - " . $company->getTranslation('title', 'kr'),
                    ],
                    'status' => true,
                    'is_public' => (bool) random_int(0, 1),
                    'winning_strategy' => collect(['immediate', 'delayed', 'hybrid'])->random(),
                    'start_date' => Carbon::now()->subDays(rand(1, 30)),
                    'end_date' => Carbon::now()->addDays(rand(15, 60)),
                    'created_by_user_id' => rand(1, 3),
                ]);

                // Attach 1–3 random platforms
                // $promotion->platforms()->attach($platforms->random(rand(1, 3))->pluck('id'));

                // Attach 1 random winner selection type

            }
        });
    }
}
