<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            [
                SocialTypeSeeder::class,
                CompanySeeder::class,
                SocialMediaSeeder::class,
                CompaniesUsersSeeder::class,
                // WinnerSelectionTypeSeeder::class,
                PlatformSeeder::class,
                PromotionSeeder::class,
                PromoGenerationSeeder::class,
                PromoCodeSeeder::class,
                MediaSeeder::class,
                ParticipationTypeSeeder::class,
                PromotionParticipationTypeSeeder::class,
                PlatformPromotionSeeder::class,
                PrizeCategoriesSeeder::class,
                PrizeSeeder::class,
                PrizePromoSeeder::class,
                SmartRandomRuleSeeder::class,
                SmartRandomValueSeeder::class,
                PrizeMessageSeeder::class,
                PromotionMessageSeeder::class,
            ]

        );
    }
}
