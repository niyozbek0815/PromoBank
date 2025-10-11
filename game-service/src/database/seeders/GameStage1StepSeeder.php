<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameStage1Step;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameStage1StepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Har bir mavjud game uchun step1-5 larni kiritamiz
        $defaultSteps = [
            1 => 5,
            2 => 4,
            3 => 3,
            4 => 2,
            5 => 1,
        ];

        Game::all()->each(function ($game) use ($defaultSteps) {
            foreach ($defaultSteps as $step => $cardCount) {
                GameStage1Step::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'step_number' => $step,
                    ],
                    [
                        'card_count' => $cardCount,
                    ]
                );
            }
        });
    }
}
