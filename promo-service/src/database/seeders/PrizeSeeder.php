<?php

namespace Database\Seeders;

use App\Models\Prize;
use App\Models\PrizeCategory;
use App\Models\Promotions;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PrizeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Barcha prize kategoriyalarni olib qo'yamiz
        $categories = PrizeCategory::all();

        // is_prize = true bo'lgan promotionlar
        $promotions = Promotions::where('is_prize', true)->get();

        foreach ($promotions as $promotion) {
            foreach ($categories as $category) {
                $prizes = [];

                for ($i = 1; $i <= 10; $i++) {
                    $prizes[] = [
                        'promotion_id' => $promotion->id,
                        'category_id' => $category->id,
                        'name' => ucfirst($category->name) . " sovg'a $i",
                        'description' => $category->description . " uchun test sovg'a",
                        'quantity' => rand(1, 10),
                        'daily_limit' => rand(1, 5),
                        'probability_weight' => rand(1, 100),
                        'is_active' => true,
                        'created_by_user_id' => rand(1, 3), // yoki tizim user
                        'valid_from' => $now,
                        'valid_until' => $now->copy()->addDays(rand(30, 90)),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                Prize::insert($prizes);
            }
        }
    }
}
