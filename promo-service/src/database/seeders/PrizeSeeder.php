<?php
namespace Database\Seeders;

use App\Models\Prize;
use App\Models\PrizeCategory;
use App\Models\Promotions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PrizeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $categories = PrizeCategory::all();
        $promotions = Promotions::whereIn('winning_strategy', ['immediate', 'hybrid'])->get();

        $randomNames = [
            'Telefon', 'Smart soat', 'Powerbank', 'Naushnik', 'Internet toʻplam', 'Kino chiptasi',
            'Oziq-ovqat bonusi', 'Aksiya sovg‘asi', 'Tashrif sovg‘asi', 'Qimmatbaho sovg‘a',
            'Yangi yil sovg‘asi', 'Maxfiy paket', 'Bonus karta', 'Kofe kupongi', 'Premium obuna',
        ];

        $randomDescriptions = [
            'Sodiqlik uchun maxsus taqdim etiladi.',
            'Tanlov orqali yutib olinadi.',
            'Mahsulot xarid qilish bilan birga beriladi.',
            'Admin tomonidan tanlanadi.',
            'Foydalanuvchi faolligiga qarab beriladi.',
            'Maxsus promokod orqali yutiladi.',
            'Chegaralangan miqdorda mavjud.',
            'Faqat tanlangan foydalanuvchilarga.',
            'Aqlli tanlov algoritm asosida belgilanadi.',
            'Har kunlik foydalanish uchun ideal.',
        ];

        foreach ($promotions as $promotion) {
            foreach ($categories as $category) {
                $tempPrizes = [];

                $count = rand(5, 12);

                for ($i = 0; $i < $count; $i++) {
                    $dailyLimit        = rand(1, 5);
                    $probabilityWeight = rand(1, 100);
                    $quantity          = max($dailyLimit, $probabilityWeight) + rand(1, 10);

                    $name        = $randomNames[array_rand($randomNames)] . ' #' . Str::upper(Str::random(3));
                    $description = $category->display_name . ' uchun: ' . $randomDescriptions[array_rand($randomDescriptions)];

                    $levelScore = ($quantity * 10) + $probabilityWeight + $dailyLimit;

                    $tempPrizes[] = [
                        'promotion_id'       => $promotion->id,
                        'category_id'        => $category->id,
                        'name'               => $name,
                        'description'        => $description,
                        'quantity'           => $quantity,
                        'daily_limit'        => $dailyLimit,
                        'probability_weight' => $probabilityWeight,
                        'is_active'          => true,
                        'created_by_user_id' => rand(1, 3),
                        'valid_from'         => $now,
                        'valid_until'        => $now->copy()->addDays(rand(30, 90)),
                        'created_at'         => $now,
                        'updated_at'         => $now,
                        '_score'             => $levelScore, // vaqtincha baholash
                    ];
                }

                // Qimmatli sovg'alarni yuqoriga chiqaramiz
                usort($tempPrizes, fn($a, $b) => $b['_score'] <=> $a['_score']);

                foreach ($tempPrizes as $i => &$prize) {
                    unset($prize['_score']);

                    // 1-chi eng muhim sovg‘aga 11 beriladi
                    $prize['index'] = ($i === 0) ? 11 : $i + 1;
                }

                Prize::insert($tempPrizes);
            }
        }
    }
}
