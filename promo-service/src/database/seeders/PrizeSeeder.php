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
                        'prize_message' => json_encode([
                            'uz' => "Tabriklaymiz, siz {$category->name} turidagi sovg'ani yutdingiz!",
                            'ru' => "Поздравляем, вы выиграли приз типа {$category->name}!",
                            'kr' => "축하합니다, {$category->name} 유형의 경품에 당첨되셨습니다!",
                        ]),
                        'is_active' => true,
                        'created_by_user_id' => rand(101, 103), // yoki tizim user
                        'valid_from' => $now,
                        'valid_until' => $now->copy()->addDays(rand(30, 90)),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Bulk insert qilish (eng optimal usul)
                Prize::insert($prizes);
            }
        }
    }
}
