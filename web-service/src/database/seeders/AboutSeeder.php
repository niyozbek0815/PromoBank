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
                'en' => 'About Us', // inglizcha qo‘shildi
            ],
            'title' => [
                'uz' => 'PromoBank - Sizning ishonchli hamkoringiz!',
                'ru' => 'PromoBank - Ваш надежный партнер!',
                'kr' => 'PromoBank - Сизнинг ишончли ҳамкорингиз!',
                'en' => 'PromoBank - Your Reliable Partner!', // inglizcha qo‘shildi
            ],
            'description' => [
                'uz' => 'PromoBank - bu innovatsion yechimlar va yuqori sifatli xizmatlar bilan mijozlarimizni qo‘llab-quvvatlaydigan ishonchli hamkor. Bizning maqsadimiz — mijozlarimizning ehtiyojlarini qondirish va ularga eng yaxshi tajribani taqdim etishdir.',
                'ru' => 'PromoBank — это надежный партнер, который поддерживает наших клиентов с помощью инновационных решений и высококачественных услуг. Наша цель — удовлетворять потребности наших клиентов и предоставлять им наилучший опыт.',
                'kr' => 'PromoBank - бу инновацион ечимлар ва юқори сифатли хизматлар билан мижозларимизни қўллаб-қувватлайдиган ишончли ҳамкор. Бизнинг мақсадимиз — мижозларимиз эҳтиёжларини қондириш ва уларга энг яхши тажрибани тақдим этиш.',
                'en' => 'PromoBank is a reliable partner supporting our clients with innovative solutions and high-quality services. Our goal is to meet our clients’ needs and provide them with the best experience.', // inglizcha qo‘shildi
            ],
            'image' => "assets/image/sponsors/1.png",
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
                'en' => [ // inglizcha qo‘shildi
                    'Innovative solutions',
                    'High-quality services',
                    'Reliable partnership',
                    'Flexible platform',
                    'Sustainable collaboration',
                    'Customer loyalty',
                ],
            ],
        ]);
    }
}
