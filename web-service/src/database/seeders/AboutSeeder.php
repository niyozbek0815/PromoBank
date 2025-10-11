<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\About;


class AboutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

About::create([
    'subtitle' => [
        'uz' => 'Biz haqimizda',
        'ru' => 'О нас',
        'kr' => 'Биз ҳақимизда',
    ],
    'title' => [
        'uz' => 'PromoBank - Sizning ishonchli hamkoringiz!',
        'ru' => 'PromoBank - Ваш надежный партнер!',
        'kr' => 'PromoBank - Сизнинг ишончли ҳамкорингиз!',
    ],
            'description' => [
                'uz' => 'PromoBank - bu innovatsion yechimlar va yuqori sifatli xizmatlar bilan mijozlarimizni qo‘llab-quvvatlaydigan ishonchli hamkor. Bizning maqsadimiz — mijozlarimizning ehtiyojlarini qondirish va ularga eng yaxshi tajribani taqdim etishdir.',
                'ru' => 'PromoBank — это надежный партнер, который поддерживает наших клиентов с помощью инновационных решений и высококачественных услуг. Наша цель — удовлетворять потребности наших клиентов и предоставлять им наилучший опыт.',
                'kr' => 'PromoBank - бу инновацион ечимлар ва юқори сифатли хизматлар билан мижозларимизни қўллаб-қувватлайдиган ишончли ҳамкор. Бизнинг мақсадимиз — мижозларимиз эҳтиёжларини қондириш ва уларга энг яхши тажрибани тақдим этиш.'
            ],
    'image'=> "assets/image/sponsors/1.png",
    'list' => [
        'uz' => [
            'Innovatsion yechimlar',
            'Yuqori sifatli xizmatlar',
            'Ishonchli hamkorlik',
            'Moslashuvchan platforma',
            'Barqaror sheriklik',
            'Mijozlarga sodiqlik',
        ],
        'ru' => [
            'Инновационные решения',
            'Высококачественные услуги',
            'Надежное партнерство',
            'Гибкая платформа',
            'Устойчивое сотрудничество',
            'Лояльность клиентов',
        ],
        'kr' => [
            'Инновацион ечимлар',
            'Юқори сифатли хизматлар',
            'Ишончли ҳамкорлик',
            'Мослашувчан платформа',
            'Барқарор шериклик',
            'Мижозларга содиқлик',
        ],
    ],
]);
    }
}
