<?php

namespace Database\Seeders;

use App\Models\Benefit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BenefitsSeeder extends Seeder
{
    public function run(): void
    {
        $benefits = [
            [
                'title' => [
                    'uz' => "Ko'p tarmoqlilik",
                    'ru' => "Многоплатформенность",
                    'kr' => "Кўп тармоқлилик",
                    'en' => "Multi-platform Support", // inglizcha qo‘shildi
                ],
                'description' => [
                    'uz' => "web, telegram, sms, mobil ilova va boshqalar.",
                    'ru' => "web, telegram, sms, мобильное приложение и другие.",
                    'kr' => "web, telegram, sms, мобил илова ва бошқалар.",
                    'en' => "web, telegram, sms, mobile app and more.", // inglizcha qo‘shildi
                ],
                'image' => 'assets/image/benefits/1.webp',
            ],
            [
                'title' => [
                    'uz' => "Promobal tizimi",
                    'ru' => "Система промобаллов",
                    'kr' => "Промобал тизими",
                    'en' => "Promo Points System",
                ],
                'description' => [
                    'uz' => "Skaner qiling bonuslarni qo‘lga kiriting.",
                    'ru' => "Сканируйте и получайте бонусы.",
                    'kr' => "Сканер қилинг бонусларни қўлга киритинг.",
                    'en' => "Scan and earn your bonus points.",
                ],
                'image' => 'assets/image/benefits/2.webp',
            ],
            [
                'title' => [
                    'uz' => "Adolatli o'yin",
                    'ru' => "Справедливая игра",
                    'kr' => "Адолатли ўйин",
                    'en' => "Fair Play",
                ],
                'description' => [
                    'uz' => "Sovg'alar adolatli shartlar asosida taqsimlanadi",
                    'ru' => "Подарки распределяются по справедливым условиям",
                    'kr' => "Совғалар адолатли шартлар асосида тақсимланади",
                    'en' => "Prizes are distributed under fair conditions.",
                ],
                'image' => 'assets/image/benefits/3.webp',
            ],
            [
                'title' => [
                    'uz' => "Promo boshqaruvi",
                    'ru' => "Управление промо",
                    'kr' => "Промо бошқаруви",
                    'en' => "Promo Management",
                ],
                'description' => [
                    'uz' => "PromoBank orqali yaratish va boshqarish.",
                    'ru' => "Создавайте и управляйте через PromoBank.",
                    'kr' => "PromoBank орқали яратиш ва бошқариш.",
                    'en' => "Create and manage promos via PromoBank.",
                ],
                'image' => 'assets/image/benefits/4.webp',
            ],
            [
                'title' => [
                    'uz' => "Sovg'ali o'yinlar",
                    'ru' => "Игры с подарками",
                    'kr' => "Совғали ўйинлар",
                    'en' => "Gift Games",
                ],
                'description' => [
                    'uz' => "Foydalanuvchilar o'zaro sovg'alar bilan almashishlari mumkin.",
                    'ru' => "Пользователи могут обмениваться подарками.",
                    'kr' => "Фойдаланувчилар ўзаро совғалар билан алмашишлари мумкин.",
                    'en' => "Users can exchange gifts with each other.",
                ],
                'image' => 'assets/image/benefits/5.webp',
            ],
            [
                'title' => [
                    'uz' => "Eng qizg'in aksiyalar",
                    'ru' => "Самые горячие акции",
                    'kr' => "Энг қизғин акциялар",
                    'en' => "Hottest Promotions",
                ],
                'description' => [
                    'uz' => "Hamma aksiyalarni bir joyda ko‘rishingiz mumkin.",
                    'ru' => "Все акции можно увидеть в одном месте.",
                    'kr' => "Ҳамма акцияларни бир жойда кўришингиз мумкин.",
                    'en' => "See all promotions in one place.",
                ],
                'image' => 'assets/image/benefits/6.webp',
            ],
        ];

        foreach ($benefits as $benefit) {
            Benefit::create($benefit);
        }
    }
}
