<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Game::create([
            'name' => json_encode(['en' => 'Treasure Hunt', 'uz' => 'Xazina Qidiruvi']),
            'title' => json_encode(['en' => 'Find the Hidden Rewards', 'uz' => 'Yashirin Sovg‘alarni Toping']),
            'about' => json_encode(['en' => 'Choose wisely and find treasures in 2 stages.', 'uz' => 'Ikkita bosqichda donolik bilan tanlab, xazinani toping.']),
            'slug' => 'treasure-hunt',
            'stage1_card_count' => 30,
            'stage2_card_count' => 10,
        ]);
    }
}
