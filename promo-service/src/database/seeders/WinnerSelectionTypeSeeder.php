<?php

namespace Database\Seeders;

use App\Models\WinnerSelectionType;
use Illuminate\Database\Seeder;

class WinnerSelectionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['manual', 'random', 'criteria'];

        foreach ($types as $type) {
            WinnerSelectionType::firstOrCreate(['name' => $type]);
        }
    }
}
