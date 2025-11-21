<?php

namespace Database\Seeders;

use App\Models\RegionLang;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionLangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            [
                'id' => 2,
                'name' => [
                    'uz' => 'Andijon',
                    'ru' => 'Андижан',
                    'en' => 'Andijan',
                    'kr' => 'Андижон',
                ],
            ],
            [
                'id' => 3,
                'name' => [
                    'uz' => 'Buxoro',
                    'ru' => 'Бухара',
                    'en' => 'Bukhara',
                    'kr' => 'Бухоро',
                ],
            ],
            [
                'id' => 12,
                'name' => [
                    'uz' => 'Farg‘ona',
                    'ru' => 'Фергана',
                    'en' => 'Fergana',
                    'kr' => 'Фарғона',
                ],
            ],
            [
                'id' => 4,
                'name' => [
                    'uz' => 'Jizzax',
                    'ru' => 'Джизак',
                    'en' => 'Jizzakh',
                    'kr' => 'Жиззах',
                ],
            ],
            [
                'id' => 7,
                'name' => [
                    'uz' => 'Namangan',
                    'ru' => 'Наманган',
                    'en' => 'Namangan',
                    'kr' => 'Наманган',
                ],
            ],
            [
                'id' => 6,
                'name' => [
                    'uz' => 'Navoiy',
                    'ru' => 'Навои',
                    'en' => 'Navoi',
                    'kr' => 'Навоий',
                ],
            ],
            [
                'id' => 5,
                'name' => [
                    'uz' => 'Qashqadaryo',
                    'ru' => 'Кашкадарья',
                    'en' => 'Kashkadarya',
                    'kr' => 'Қашқадарё',
                ],
            ],
            [
                'id' => 1,
                'name' => [
                    'uz' => 'Qoraqalpog‘iston Respublikasi',
                    'ru' => 'Республика Каракалпакстан',
                    'en' => 'Republic of Karakalpakstan',
                    'kr' => 'Қорақалпоғистон Республикаси',
                ],
            ],
            [
                'id' => 8,
                'name' => [
                    'uz' => 'Samarqand',
                    'ru' => 'Самарканд',
                    'en' => 'Samarkand',
                    'kr' => 'Самарқанд',
                ],
            ],
            [
                'id' => 10,
                'name' => [
                    'uz' => 'Sirdaryo',
                    'ru' => 'Сырдарья',
                    'en' => 'Syrdarya',
                    'kr' => 'Сирдарё',
                ],
            ],
            [
                'id' => 9,
                'name' => [
                    'uz' => 'Surxandaryo',
                    'ru' => 'Сурхандарья',
                    'en' => 'Surkhandarya',
                    'kr' => 'Сурхандарё',
                ],
            ],
            [
                'id' => 14,
                'name' => [
                    'uz' => 'Toshkent shahri',
                    'ru' => 'Город Ташкент',
                    'en' => 'Tashkent City',
                    'kr' => 'Тошкент шаҳри',
                ],
            ],
            [
                'id' => 11,
                'name' => [
                    'uz' => 'Toshkent viloyati',
                    'ru' => 'Ташкентская область',
                    'en' => 'Tashkent Region',
                    'kr' => 'Тошкент вилояти',
                ],
            ],
            [
                'id' => 13,
                'name' => [
                    'uz' => 'Xorazm',
                    'ru' => 'Хорезм',
                    'en' => 'Khorezm',
                    'kr' => 'Хоразм',
                ],
            ],
        ];

        foreach ($regions as $region) {
            RegionLang::updateOrCreate(['id' => $region['id']], $region);
        }
    }
}
