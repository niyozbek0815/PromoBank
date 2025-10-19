<?php

namespace Database\Seeders;

use App\Models\ForSponsor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ForSponsorsSeeder extends Seeder
{
    public function run(): void
    {
        $forSponsors = [
            [
                'title' => [
                    'uz' => 'Maksimal ko‘rinish',
                    'ru' => 'Максимальная видимость',
                    'kr' => 'Максимал кўриниш',
                    'en' => 'Maximum visibility',
                ],
                'desc' => [
                    'uz' => 'Brendingiz barcha foydalanuvchilarga keng targ‘ib qilinadi.',
                    'ru' => 'Ваш бренд будет широко продвигаться среди всех пользователей.',
                    'kr' => 'Брендингиз барча фойдаланувчиларга кенг тарғиб қилинади.',
                    'en' => 'Your brand will be widely promoted to all users.',
                ],
                'img' => 'assets/image/forSponsors/1.svg',
            ],
            [
                'title' => [
                    'uz' => 'Aniq ma’lumotlar',
                    'ru' => 'Точные данные',
                    'kr' => 'Аниқ маълумотлар',
                    'en' => 'Accurate data',
                ],
                'desc' => [
                    'uz' => 'Har bir kampaniya bo‘yicha to‘liq statistika taqdim etiladi.',
                    'ru' => 'Предоставляется полная статистика по каждой кампании.',
                    'kr' => 'Ҳар бир кампания бўйича тўлиқ статистика тақдим этилади.',
                    'en' => 'Complete statistics are provided for each campaign.',
                ],
                'img' => 'assets/image/forSponsors/2.svg',
            ],
            [
                'title' => [
                    'uz' => 'Ma’qsadli auditoriya',
                    'ru' => 'Целевая аудитория',
                    'kr' => 'Мақсадли аудитория',
                    'en' => 'Target audience',
                ],
                'desc' => [
                    'uz' => 'Promolar aynan kerakli mijoz segmentiga yetkaziladi.',
                    'ru' => 'Промоакции доставляются именно нужному сегменту клиентов.',
                    'kr' => 'Промолар аниқ керакли мижоз сегментига етказилади.',
                    'en' => 'Promotions reach the exact customer segment needed.',
                ],
                'img' => 'assets/image/forSponsors/3.svg',
            ],
            [
                'title' => [
                    'uz' => 'Onlayn nazorat',
                    'ru' => 'Онлайн-контроль',
                    'kr' => 'Онлайн назорат',
                    'en' => 'Online monitoring',
                ],
                'desc' => [
                    'uz' => 'Jarayonlarni real vaqt rejimida kuzatish imkoniyati mavjud.',
                    'ru' => 'Есть возможность отслеживать процессы в реальном времени.',
                    'kr' => 'Жараёнларни реал вақтда кузатиш имконияти мавжуд.',
                    'en' => 'Ability to track processes in real-time.',
                ],
                'img' => 'assets/image/forSponsors/4.svg',
            ],
            [
                'title' => [
                    'uz' => 'Innovatsion texnologiya',
                    'ru' => 'Инновационная технология',
                    'kr' => 'Инновацион технология',
                    'en' => 'Innovative technology',
                ],
                'desc' => [
                    'uz' => 'Platforma tezkor, xavfsiz va zamonaviy yechimlarga asoslangan.',
                    'ru' => 'Платформа основана на быстрых, безопасных и современных решениях.',
                    'kr' => 'Платформа тезкор, хавфсиз ва замонавий ечимларга асосланган.',
                    'en' => 'The platform is based on fast, secure, and modern solutions.',
                ],
                'img' => 'assets/image/forSponsors/5.svg',
            ],
            [
                'title' => [
                    'uz' => 'Moslashuvchan sozlamalar',
                    'ru' => 'Гибкие настройки',
                    'kr' => 'Мослашувчан созламалар',
                    'en' => 'Flexible settings',
                ],
                'desc' => [
                    'uz' => 'Promolarni istalgan talab va sharoitga moslab tuzish mumkin.',
                    'ru' => 'Промо можно настроить под любые требования и условия.',
                    'kr' => 'Промоларни исталган талаб ва шароитга мослаб тузиш мумкин.',
                    'en' => 'Promotions can be customized to any requirement or condition.',
                ],
                'img' => 'assets/image/forSponsors/6.svg',
            ],
            [
                'title' => [
                    'uz' => 'Uzoq muddatli hamkorlik',
                    'ru' => 'Долгосрочное сотрудничество',
                    'kr' => 'Узоқ муддатли ҳамкорлик',
                    'en' => 'Long-term partnership',
                ],
                'desc' => [
                    'uz' => 'Biznesingiz o‘sishi uchun barqaror va ishonchli sheriklik.',
                    'ru' => 'Стабильное и надежное партнёрство для роста вашего бизнеса.',
                    'kr' => 'Бизнесингиз ўсиши учун барқарор ва ишончли шериклик.',
                    'en' => 'Stable and reliable partnership for business growth.',
                ],
                'img' => 'assets/image/forSponsors/7.svg',
            ],
            [
                'title' => [
                    'uz' => 'Qo‘shimcha imtiyozlar',
                    'ru' => 'Дополнительные привилегии',
                    'kr' => 'Қўшимча имтиёзлар',
                    'en' => 'Additional benefits',
                ],
                'desc' => [
                    'uz' => 'Homiylarga eksklyuziv bonus va chegirmalar beriladi.',
                    'ru' => 'Спонсорам предоставляются эксклюзивные бонусы и скидки.',
                    'kr' => 'Ҳомийларга эксклюзив бонус ва чегирмалар берилади.',
                    'en' => 'Sponsors receive exclusive bonuses and discounts.',
                ],
                'img' => 'assets/image/portfolio/bg-item-1.jpg',
            ],
        ];

        foreach ($forSponsors as $index => $item) {
            ForSponsor::updateOrCreate(
                ['title->uz' => $item['title']], // unique check
                [
                    'title' => $item['title'],
                    'description' => $item['desc'],
                    'image' => $item['img'],
                    'position' => $index + 1,
                    'status' => 1,
                ]
            );
        }
    }
}
