<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocialTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Telegram'],
            ['name' => 'Instagram'],
            ['name' => 'Facebook'],
            ['name' => 'YouTube'],
            ['name' => 'LinkedIn'],
            ['name' => 'Website'],
        ];

        DB::table('social_types')->insert($types);
    }
}
