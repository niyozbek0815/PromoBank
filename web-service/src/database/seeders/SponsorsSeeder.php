<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SponsorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sponsors = [
            ['url' => '#', 'img' => 'assets/image/sponsors/2.avif', 'alt' => 'Sponsor 1'],
            ['url' => '#', 'img' => 'assets/image/sponsors/3.jpg',  'alt' => 'Sponsor 2'],
            ['url' => '#', 'img' => 'assets/image/sponsors/4.jpg',  'alt' => 'Sponsor 3'],
            ['url' => '#', 'img' => 'assets/image/sponsors/5.jpg',  'alt' => 'Sponsor 4'],
            ['url' => '#', 'img' => 'assets/image/sponsors/6.jpg',  'alt' => 'Sponsor 5'],
            ['url' => '#', 'img' => 'assets/image/sponsors/7.jpg',  'alt' => 'Sponsor 6'],
            ['url' => '#', 'img' => 'assets/image/sponsors/8.jpg',  'alt' => 'Sponsor 7'],
            ['url' => '#', 'img' => 'assets/image/sponsors/9.jpg',  'alt' => 'Sponsor 8'],
        ];

        foreach ($sponsors as $index => $data) {
            Sponsor::updateOrCreate(
                ['name->uz' => $data['alt']], // unikallik tekshiruvi
                [
                    'name'   => [
                        'uz' => $data['alt'],
                        'ru' => $data['alt'],
                        'kr' => $data['alt'],
                        'en' => $data['alt'],
                    ],
                    'url'    => $data['url'],
                    'weight' => $index + 1,
                    'status' => 1,
                    'image'  => $data['img'],
                ]
            );
        }
    }
}
