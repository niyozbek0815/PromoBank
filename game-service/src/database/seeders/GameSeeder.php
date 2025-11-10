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
            'name' => [
                'en' => 'Zinama Zina',
                'uz' => 'Zinama Zina',
                'kr' => 'Zinama Zina',
                'ru' => 'Зинама Зина',
            ],
            'title' => [
                'en' => 'Discover Hidden Rewards',
                'uz' => 'Yashirin sovg‘alarni toping',
                'kr' => 'Yashirin sovg‘alarni toping',
                'ru' => 'Откройте скрытые награды',
            ],
            'about' => [
                'en' => 'Play wisely across 2 stages to uncover treasures.',
                'uz' => 'Ikkita bosqichda donolik bilan o‘ynab, xazinani toping.',
                'kr' => 'Ikkita bosqichda donolik bilan o‘ynab, xazinani toping.',
                'ru' => 'Играйте мудро в 2 этапа, чтобы найти сокровища.',
            ],
            'slug' => 'treasure-hunt',
            'stage1_card_count' => 30,
            'stage2_card_count' => 10,
        ]);
    }
}
