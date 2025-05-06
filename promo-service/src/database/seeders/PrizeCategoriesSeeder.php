<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrizeCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            [
                'name' => 'manual',
                'description' => 'Prize category assigned manually',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'smart_random',
                'description' => 'Prize category assigned by smart random algorithm',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'auto_bind',
                'description' => 'Prize category bound automatically by system',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('prize_categories')->insert($categories);
    }
}
