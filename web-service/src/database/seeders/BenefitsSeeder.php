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
                    'kr' => "Кўп тармоқлилик"
                ],
                'description' => [
                    'uz' => "web, telegram, sms, mobil ilova va boshqalar.",
                    'ru' => "web, telegram, sms, мобильное приложение и другие.",
                    'kr' => "web, telegram, sms, мобил илова ва бошқалар."
                ],
                'image' => 'assets/image/benefits/1.webp',
            ],
            [
                'title' => [
                    'uz' => "Promobal tizimi",
                    'ru' => "Система промобаллов",
                    'kr' => "Промобал тизими"
                ],
                'description' => [
                    'uz' => "Skaner qiling bonuslarni qo‘lga kiriting.",
                    'ru' => "Сканируйте и получайте бонусы.",
                    'kr' => "Сканер қилинг бонусларни қўлга киритинг."
                ],
                'image' => 'assets/image/benefits/2.webp',
            ],
            [
                'title' => [
                    'uz' => "Adolatli o'yin",
                    'ru' => "Справедливая игра",
                    'kr' => "Адолатли ўйин"
                ],
                'description' => [
                    'uz' => "Sovg'alar adolatli shartlar asosida taqsimlanadi",
                    'ru' => "Подарки распределяются по справедливым условиям",
                    'kr' => "Совғалар адолатли шартлар асосида тақсимланади"
                ],
                'image' => 'assets/image/benefits/3.webp',
            ],
            [
                'title' => [
                    'uz' => "Promo boshqaruvi",
                    'ru' => "Управление промо",
                    'kr' => "Промо бошқаруви"
                ],
                'description' => [
                    'uz' => "PromoBank orqali yaratish va boshqarish.",
                    'ru' => "Создавайте и управляйте через PromoBank.",
                    'kr' => "PromoBank орқали яратиш ва бошқариш."
                ],
                'image' => 'assets/image/benefits/4.webp',
            ],
            [
                'title' => [
                    'uz' => "Sovg'ali o'yinlar",
                    'ru' => "Игры с подарками",
                    'kr' => "Совғали ўйинлар"
                ],
                'description' => [
                    'uz' => "Foydalanuvchilar o'zaro sovg'alar bilan almashishlari mumkin.",
                    'ru' => "Пользователи могут обмениваться подарками.",
                    'kr' => "Фойдаланувчилар ўзаро совғалар билан алмашишлари мумкин."
                ],
                'image' => 'assets/image/benefits/5.webp',
            ],
            [
                'title' => [
                    'uz' => "Eng qizg'in aksiyalar",
                    'ru' => "Самые горячие акции",
                    'kr' => "Энг қизғин акциялар"
                ],
                'description' => [
                    'uz' => "Hamma aksiyalarni bir joyda ko‘rishingiz mumkin.",
                    'ru' => "Все акции можно увидеть в одном месте.",
                    'kr' => "Ҳамма акцияларни бир жойда кўришингиз мумкин."
                ],
                'image' => 'assets/image/benefits/6.webp',
            ],
        ];

        foreach ($benefits as $benefit) {
            Benefit::create($benefit);
        }
    }
}
