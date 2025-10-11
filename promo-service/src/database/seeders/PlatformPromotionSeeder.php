<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\PlatformPromotion;
use App\Models\Promotions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformPromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = Platform::pluck('id')->toArray();
        $promotions = Promotions::all();

        foreach ($promotions as $promo) {
            $count = rand(1, 3); // har bir promotionga 1-3 ta platform
            $selectedPlatforms = collect($platforms)->random($count);

            foreach ($selectedPlatforms as $platformId) {
                PlatformPromotion::create([
                    'promotion_id' => $promo->id,
                    'platform_id' => $platformId,
                    'is_enabled' => (bool)rand(0, 1),
                    'additional_rules' => [
                        'limit' => rand(1, 5),
                        'region' => fake()->countryCode(),
                    ],
                ]);
            }
        }
    }
}
