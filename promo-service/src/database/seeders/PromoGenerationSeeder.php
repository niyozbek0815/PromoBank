<?php

namespace Database\Seeders;

use App\Models\PromoGeneration;
use App\Models\Promotions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromoGenerationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotions = Promotions::all();

        foreach ($promotions as $promotion) {
            PromoGeneration::create([
                'promotion_id' => $promotion->id,
                'created_by_user_id' => 1,
                'type'=>'generated',
            ]);
        }
    }
}
