<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Portfolio;

class PortfoliosSeeder extends Seeder
{
    public function run(): void
    {
        $portfolios = [
            [
                'title' => [
                    'uz' => 'Coca-Cola — “Qadoqni ochib, sovrin yut!”',
                    'ru' => 'Coca-Cola — "Открой упаковку и выиграй приз!"',
                    'kr' => 'Coca-Cola — “Қадоқни очиб, соврин ютиш!”',
                    'en' => 'Coca-Cola — "Open the pack and win a prize!"',
                ],
                'subtitle' => [
                    'uz' => 'Promoaksiya',
                    'ru' => 'Промоакция',
                    'kr' => 'Промоакция',
                    'en' => 'Promo campaign',
                ],
                'image' => 'assets/image/portfolio/bg-item-1.jpg',
            ],
            [
                'title' => [
                    'uz' => 'Milliy TV — “Yangi yil super o‘yinlari”',
                    'ru' => 'Milliy TV — "Супер игры на Новый год"',
                    'kr' => 'Milliy TV — “Янги йил супер ўйинлари”',
                    'en' => 'Milliy TV — "New Year Super Games"',
                ],
                'subtitle' => [
                    'uz' => 'Teleshou',
                    'ru' => 'Телешоу',
                    'kr' => 'Телешоу',
                    'en' => 'TV Show',
                ],
                'image' => 'assets/image/portfolio/bg-item-2.jpg',
            ],
            [
                'title' => [
                    'uz' => 'Artel — “Yozgi chegirmalar marafoni 20%”',
                    'ru' => 'Artel — "Летний марафон скидок 20%"',
                    'kr' => 'Artel — “Ёзги чегирмалар марафони 20%”',
                    'en' => 'Artel — "Summer Discount Marathon 20%"',
                ],
                'subtitle' => [
                    'uz' => 'Artel',
                    'ru' => 'Artel',
                    'kr' => 'Artel',
                    'en' => 'Artel',
                ],
                'image' => 'assets/image/portfolio/bg-item-3.jpg',
            ],
            [
                'title' => [
                    'uz' => 'Korzinka — “Har hafta sovrinli lotereya”',
                    'ru' => 'Korzinka — "Еженедельная лотерея с призами"',
                    'kr' => 'Korzinka — “Ҳар ҳафта совринли лотерея”',
                    'en' => 'Korzinka — "Weekly prize lottery"',
                ],
                'subtitle' => [
                    'uz' => 'Korzinka',
                    'ru' => 'Korzinka',
                    'kr' => 'Korzinka',
                    'en' => 'Korzinka',
                ],
                'image' => 'assets/image/portfolio/bg-item-4.jpg',
            ],
            [
                'title' => [
                    'uz' => 'Beeline Uzbekistan — “5GB bonus internet sovg‘a!”',
                    'ru' => 'Beeline Uzbekistan — "Бонусный интернет 5GB!"',
                    'kr' => 'Beeline Uzbekistan — “5GB бонус интернет совға!”',
                    'en' => 'Beeline Uzbekistan — "5GB bonus internet gift!"',
                ],
                'subtitle' => [
                    'uz' => 'Beeline Uzbekistan',
                    'ru' => 'Beeline Uzbekistan',
                    'kr' => 'Beeline Uzbekistan',
                    'en' => 'Beeline Uzbekistan',
                ],
                'image' => 'assets/image/portfolio/1-5.png',
            ],
        ];

        foreach ($portfolios as $index => $data) {
            Portfolio::create(array_merge($data, ['position' => $index + 1, 'status' => 1]));
        }
    }
}
