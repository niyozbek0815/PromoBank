<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocialMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            ['company_id' => 1, 'type_id' => 1, 'url' => 'https://t.me/artel_official'],
            ['company_id' => 1, 'type_id' => 2, 'url' => 'https://instagram.com/artel'],
            ['company_id' => 2, 'type_id' => 1, 'url' => 'https://t.me/beelineuz'],
            ['company_id' => 2, 'type_id' => 2, 'url' => 'https://instagram.com/beelineuz'],
            ['company_id' => 3, 'type_id' => 1, 'url' => 'https://t.me/texnomart'],
            ['company_id' => 3, 'type_id' => 2, 'url' => 'https://instagram.com/texnomart'],
        ];

        DB::table('social_media')->insert($records);
    }
}
