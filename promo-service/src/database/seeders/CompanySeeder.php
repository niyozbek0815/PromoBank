<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'user_id' => 101,
                'name' => [
                    'uz' => 'Artel Group',
                    'ru' => 'Артель Групп',
                    'kr' => '아르텔 그룹',
                ],
                'title' => [
                    'uz' => 'Maishiy texnika ishlab chiqaruvchisi',
                    'ru' => 'Производитель бытовой техники',
                    'kr' => '가전 제품 제조업체',
                ],
                'description' => [
                    'uz' => 'Artel — O‘zbekistonning eng yirik texnika ishlab chiqaruvchilardan biri.',
                    'ru' => 'Artel — один из крупнейших производителей техники в Узбекистане.',
                    'kr' => '아르텔은 우즈베키스탄에서 가장 큰 전자제품 제조업체 중 하나입니다.',
                ],
                'email' => 'info@artelgroup.uz',
                'settings' => json_encode(['language' => 'uz', 'timezone' => 'Asia/Tashkent']),
                'status' => 'active',
                'region' => 'Toshkent shahri',
                'address' => 'Toshkent, Chilonzor 5-daha, 10-uy',
                'created_by_user_id' => 101,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 102,
                'name' => [
                    'uz' => 'Beeline Uzbekistan',
                    'ru' => 'Билайн Узбекистан',
                    'kr' => '비라인 우즈베키스탄',
                ],
                'title' => [
                    'uz' => 'Mobil aloqa operatori',
                    'ru' => 'Оператор мобильной связи',
                    'kr' => '모바일 통신 사업자',
                ],
                'description' => [
                    'uz' => 'Beeline — O‘zbekistondagi yetakchi mobil aloqa va internet provayderi.',
                    'ru' => 'Beeline — ведущий мобильный оператор и интернет-провайдер в Узбекистане.',
                    'kr' => '비라인은 우즈베키스탄의 주요 모바일 및 인터넷 제공업체입니다.',
                ],
                'email' => 'support@beeline.uz',
                'settings' => json_encode(['language' => 'ru', 'support_24_7' => true]),
                'status' => 'active',
                'region' => 'Toshkent viloyati',
                'address' => 'Yunusobod tumani, Amir Temur ko‘chasi 107',
                'created_by_user_id' => 102,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 103,
                'name' => [
                    'uz' => 'Texnomart',
                    'ru' => 'Техномарт',
                    'kr' => '텍노마트',
                ],
                'title' => [
                    'uz' => 'Maishiy texnika do‘konlar tarmog‘i',
                    'ru' => 'Сеть магазинов бытовой техники',
                    'kr' => '가전 제품 매장 네트워크',
                ],
                'description' => [
                    'uz' => 'Texnomart — butun respublika bo‘ylab texnika mahsulotlari savdosi.',
                    'ru' => 'Texnomart — сеть магазинов техники по всей республике.',
                    'kr' => '텍노마트는 전국에 걸쳐 전자 제품을 판매합니다.',
                ],
                'email' => 'contact@texnomart.uz',
                'settings' => json_encode(['delivery' => true, 'payment_types' => ['cash', 'card']]),
                'status' => 'inactive',
                'region' => 'Samarqand viloyati',
                'address' => 'Registon ko‘chasi, 21',
                'created_by_user_id' => 103,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        // Translatable ustunlarni JSON ko‘rinishga keltirish
        foreach ($companies as &$company) {
            $company['name'] = json_encode($company['name'], JSON_UNESCAPED_UNICODE);
            $company['title'] = json_encode($company['title'], JSON_UNESCAPED_UNICODE);
            $company['description'] = json_encode($company['description'], JSON_UNESCAPED_UNICODE);
        }

        DB::table('companies')->insert($companies);
    }
}
