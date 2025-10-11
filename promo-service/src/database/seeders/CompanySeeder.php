<?php

namespace Database\Seeders;

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
                    'en' => 'Artel Group',
                    'kr' => 'Артел Групп',
                ],
                'title' => [
                    'uz' => 'Maishiy texnika ishlab chiqaruvchisi',
                    'ru' => 'Производитель бытовой техники',
                    'en' => 'Home appliance manufacturer',
                    'kr' => 'Маиший техника ишлаб чиқарувчиси',
                ],
                'description' => [
                    'uz' => 'Artel — O‘zbekistonning eng yirik texnika ishlab chiqaruvchilardan biri.',
                    'ru' => 'Artel — один из крупнейших производителей техники в Узбекистане.',
                    'en' => 'Artel is one of the largest appliance manufacturers in Uzbekistan.',
                    'kr' => 'Артел — Ўзбекистоннинг энг йирик техника ишлаб чиқарувчиларидан бири.',
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
                    'en' => 'Beeline Uzbekistan',
                    'kr' => 'Билайн Ўзбекистон',
                ],
                'title' => [
                    'uz' => 'Mobil aloqa operatori',
                    'ru' => 'Оператор мобильной связи',
                    'en' => 'Mobile network operator',
                    'kr' => 'Мобил алоқа операторы',
                ],
                'description' => [
                    'uz' => 'Beeline — O‘zbekistondagi yetakchi mobil aloqa va internet provayderi.',
                    'ru' => 'Beeline — ведущий мобильный оператор и интернет-провайдер в Узбекистане.',
                    'en' => 'Beeline is a leading mobile operator and internet provider in Uzbekistan.',
                    'kr' => 'Билайн — Ўзбекистондаги етакчи мобил алоқа ва интернет провайдери.',
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
                    'en' => 'Texnomart',
                    'kr' => 'Техномарт',
                ],
                'title' => [
                    'uz' => 'Maishiy texnika do‘konlar tarmog‘i',
                    'ru' => 'Сеть магазинов бытовой техники',
                    'en' => 'Chain of appliance stores',
                    'kr' => 'Маиший техника дўконлар тармоғи',
                ],
                'description' => [
                    'uz' => 'Texnomart — butun respublika bo‘ylab texnika mahsulotlari savdosi.',
                    'ru' => 'Texnomart — сеть магазинов техники по всей республике.',
                    'en' => 'Texnomart is a nationwide retail chain selling electronics and appliances.',
                    'kr' => 'Техномарт — бутун республика бўйлаб техника маҳсулотлари савдоси.',
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

        // Translatable ustunlarni JSON formatga o‘tkazamiz
        foreach ($companies as &$company) {
            $company['name'] = json_encode($company['name'], JSON_UNESCAPED_UNICODE);
            $company['title'] = json_encode($company['title'], JSON_UNESCAPED_UNICODE);
            $company['description'] = json_encode($company['description'], JSON_UNESCAPED_UNICODE);
        }

        DB::table('companies')->insert($companies);
    }
}
