<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\SocialLink;
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
        $seeders = [
            SettingsSeeder::class,
            DownloadSeeder::class,
            AboutSeeder::class,
            SocialLinkSeeder::class,
            ContactsSeeder::class,
            BenefitsSeeder::class,
            SponsorsSeeder::class,
            ForSponsorsSeeder::class,
            PortfoliosSeeder::class,
        ];

        // Hammasini bir qatorcha chaqirish
        $this->call($seeders);
    }
}
