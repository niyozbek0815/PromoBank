<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\PromoCode;
use App\Models\PromoGeneration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromoCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = Platform::all();

        PromoGeneration::with('promotion')->get()->each(function ($generation) use ($platforms) {
            for ($i = 0; $i < 200; $i++) {
                PromoCode::create([
                    'generation_id' => $generation->id,
                    'promotion_id' => $generation->promotion_id,
                    'promocode' => strtoupper(Str::random(10)),
                    'is_used' => false,
                    'used_at' => null,
                ]);
            }
        });
    }
}
