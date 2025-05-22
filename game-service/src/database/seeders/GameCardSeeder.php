<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameCard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $game = Game::first(); // faqat bitta mavjud game uchun

        if (!$game) return;

        $cards = [
            // Stage 1
            ['point' => 10,   'stage' => 'stage1', 'frequency' => 10],
            ['point' => 50,   'stage' => 'stage1', 'frequency' => 8],
            ['point' => 100,  'stage' => 'stage1', 'frequency' => 6],
            ['point' => 200,  'stage' => 'stage1', 'frequency' => 4],
            ['point' => 300,  'stage' => 'stage1', 'frequency' => 2],

            // Stage 2
            ['point' => 500,   'stage' => 'stage2', 'frequency' => 6],
            ['point' => 1000,  'stage' => 'stage2', 'frequency' => 2],
            ['point' => 2000,  'stage' => 'stage2', 'frequency' => 1],
            ['point' => 2500,  'stage' => 'stage2', 'frequency' => 1],
        ];

        foreach ($cards as $data) {
            GameCard::create([
                'game_id' => $game->id,
                'point' => $data['point'],
                'stage' => $data['stage'],
                'frequency' => $data['frequency'],
            ]);
        }
    }
}