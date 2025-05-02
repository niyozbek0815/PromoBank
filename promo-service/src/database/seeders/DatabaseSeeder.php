<?php

namespace Database\Seeders;

use App\Models\Promotions;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                WinnerSelectionTypeSeeder::class,
                PlatformSeeder::class,
                PromotionSeeder::class,
                PromoGenerationSeeder::class,
                PromoCodeSeeder::class,
                MediaSeeder::class
            ]
        );
    }
}