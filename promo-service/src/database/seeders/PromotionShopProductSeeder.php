<?php

namespace Database\Seeders;

use App\Models\Promotions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromotionShopProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotions = Promotions::whereHas('platforms', function ($query) {
            $query->where('name', 'mobile');
        })
            ->whereHas('participationTypes.participationType', function ($query) {
                $query->whereIn('slug', ['receipt_scan']);
            })
            ->get();

        foreach ($promotions as $promotion) {
            for ($i = 1; $i <= 10; $i++) {
                $shop = $promotion->shops()->create([
                    'name'   => "Shop #$i for Promotion #{$promotion->id}",
                    'adress' => "Address #$i",
                ]);

                for ($j = 1; $j <= 4; $j++) {
                    $shop->products()->create([
                        'promotion_id' => $promotion->id,
                        'name'         => "Product $j"
                    ]);
                }
            }
        }
    }
}