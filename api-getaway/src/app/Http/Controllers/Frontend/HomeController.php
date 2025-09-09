<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $socialLinks = [
            [
                'type' => 'instagram',
                'url'  => 'https://instagram.com/yourpage'
            ],
            [
                'type' => 'facebook',
                'url'  => 'https://facebook.com/yourpage'
            ],
            [
                'type' => 'telegram',
                'url'  => 'https://t.me/yourchannel'
            ],
            [
                'type' => 'youtube',
                'url'  => 'https://youtube.com/yourchannel'
            ],
            [
                'type' => 'appstore',
                'url'  => 'https://apps.apple.com/yourapp'
            ],
            [
                'type' => 'googleplay',
                'url'  => 'https://play.google.com/store/apps/details?id=yourapp'
            ]
        ];
        $contacts = [
            [
                'url'  => 'https://maps.google.com/?q=Toshkent, Amir Temur ko‘chasi, 15-uy',
                'label' => 'Toshkent, Amir Temur ko‘chasi, 15-uy'
            ],
            [
                'url'  => 'tel:+998901234567',
                'label' => '+998 (90) 123-45-67'
            ],
            [
                'url'  => 'mailto:support@promobank.uz',
                'label' => 'support@promobank.uz'
            ],
        ];
        $heroTitle = 'Har xaridda imkoniyat: o‘yna, yut, quvon';
        $promos = [
            ['title' => 'Beeline — "1 oy bepul internet" aksiyasi', 'img' => 'assets/image/promotion/1.gif'],
            ['title' => 'Artel — "Yozgi chegirmalar marafoni 20%"', 'img' => 'assets/image/promotion/13e5cd704f9ed67fc562481be03db292.jpg'],
            ['title' => 'Coca-Cola Uzbekistan — "Qadoqni ochib yut!"', 'img' => 'assets/image/promotion/3cde50d623fc002fcab5bfba3bc72743.jpg'],
            ['title' => 'Pepsi Uzbekistan — "1ta sotib ol, 2chisi sovg‘a"', 'img' => 'assets/image/promotion/8817b552c2d5ff638921e8c6fdf9280d.jpg'],
            ['title' => 'Carrefour — "Hafta oxiri maxsus chegirmalar"', 'img' => 'assets/image/promotion/_.jpeg'],
            ['title' => 'Ucell — "10GB bonus internet" lotereyasi', 'img' => 'assets/image/promotion/winter_exclusive_for_man-cover.png'],
            ['title' => 'Korzinka — "Black Friday 50% gacha chegirma"', 'img' => 'assets/image/promotion/Modelo de banner preto de venda na sexta-feira com sacos de loja em 3D e cubos flutuando até o carrinho de compras _ PSD Premium.jpeg'],
        ];
        $download = [
            'subtitle'    => 'Yuklab olish va kuzatish',
            'title'       => 'PromoBank bilan tez va oson yutib oling!',
            'description' => 'PromoBank mobil ilovasi va Telegram bot orqali barcha aksiyalarda qatnashing,
                      yutuqlarni kuzating va kodlaringizni saqlang. Hoziroq yuklab oling!',
            'image'       => 'assets/image/download/intro-mobile.png',
            'links' => [
                [
                    'type' => 'googleplay',
                    'url'  => 'https://play.google.com/store'
                ],
                [
                    'type' => 'appstore',
                    'url'  => 'https://apps.apple.com/'
                ],
                [
                    'type' => 'telegram',
                    'url'  => 'https://t.me/your_promobank_bot'
                ],
            ]
        ];

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

        // 🔥 Foyda/Imkoniyatlar (benefits)
        $benefits = [
            [
                'title' => "Ko'p tarmoqlilik",
                'desc'  => "web, telegram, sms, mobil ilova va boshqalar.",
                'img'   => 'assets/image/benefits/1.webp',
            ],
            [
                'title' => "Promobal tizimi",
                'desc'  => "Skaner qiling bonuslarni qo‘lga kiriting.",
                'img'   => 'assets/image/benefits/2.webp',
            ],
            [
                'title' => "Adolatli o'yin",
                'desc'  => "Sovg'alar adolatli shartlar asosida taqsimlanadi",
                'img'   => 'assets/image/benefits/3.webp',
            ],
            [
                'title' => "Promo boshqaruvi",
                'desc'  => "PromoBank orqali yaratish va boshqarish.",
                'img'   => 'assets/image/benefits/4.webp',
            ],
            [
                'title' => "Sovg'ali o'yinlar",
                'desc'  => "Foydalanuvchilar o'zaro sovg'alar bilan almashishlari mumkin.",
                'img'   => 'assets/image/benefits/5.webp',
            ],
            [
                'title' => "Eng qizg'in aksiyalar",
                'desc'  => "Hamma aksiyalarni bir joyda ko‘rishingiz mumkin.",
                'img'   => 'assets/image/benefits/6.webp',
            ],
        ];

        // 🔥 Portfolio loyihalari
        $portfolios = [
            [
                'title'    => 'Coca-Cola — “Qadoqni ochib, sovrin yut!”',
                'subtitle' => 'Promoaksiya',
                'img'      => 'assets/image/portfolio/bg-item-1.jpg',
            ],
            [
                'title'    => 'Milliy TV — “Yangi yil super o‘yinlari”',
                'subtitle' => 'Teleshou',
                'img'      => 'assets/image/portfolio/bg-item-2.jpg',
            ],
            [
                'title'    => 'Artel — “Yozgi chegirmalar marafoni 20%”',
                'subtitle' => 'Artel',
                'img'      => 'assets/image/portfolio/bg-item-3.jpg',
            ],
            [
                'title'    => 'Korzinka — “Har hafta sovrinli lotereya”',
                'subtitle' => 'Korzinka',
                'img'      => 'assets/image/portfolio/bg-item-4.jpg',
            ],
            [
                'title'    => 'Beeline Uzbekistan — “5GB bonus internet sovg‘a!”',
                'subtitle' => 'Beeline Uzbekistan',
                'img'      => 'assets/image/portfolio/1-5.png',
            ],
        ];

        // 🔥 Homiylarga imkoniyatlar (for-sponsors)
        $forSponsors = [
            [
                'title' => 'Maksimal ko‘rinish',
                'desc'  => 'Brendingiz barcha foydalanuvchilarga keng targ‘ib qilinadi.',
                'img'   => 'assets/image/forSponsors/1.svg',
            ],
            [
                'title' => 'Aniq ma’lumotlar',
                'desc'  => 'Har bir kampaniya bo‘yicha to‘liq statistika taqdim etiladi.',
                'img'   => 'assets/image/forSponsors/2.svg',
            ],
            [
                'title' => 'Ma’qsadli auditoriya',
                'desc'  => 'Promolar aynan kerakli mijoz segmentiga yetkaziladi.',
                'img'   => 'assets/image/forSponsors/3.svg',
            ],
            [
                'title' => 'Onlayn nazorat',
                'desc'  => 'Jarayonlarni real vaqt rejimida kuzatish imkoniyati mavjud.',
                'img'   => 'assets/image/forSponsors/4.svg',
            ],
            [
                'title' => 'Innovatsion texnologiya',
                'desc'  => 'Platforma tezkor, xavfsiz va zamonaviy yechimlarga asoslangan.',
                'img'   => 'assets/image/forSponsors/5.svg',
            ],
            [
                'title' => 'Moslashuvchan sozlamalar',
                'desc'  => 'Promolarni istalgan talab va sharoitga moslab tuzish mumkin.',
                'img'   => 'assets/image/forSponsors/6.svg',
            ],
            [
                'title' => 'Uzoq muddatli hamkorlik',
                'desc'  => 'Biznesingiz o‘sishi uchun barqaror va ishonchli sheriklik.',
                'img'   => 'assets/image/forSponsors/7.svg',
            ],
            [
                'title' => 'Qo‘shimcha imtiyozlar',
                'desc'  => 'Homiylarga eksklyuziv bonus va chegirmalar beriladi.',
                'img'   => 'assets/image/portfolio/bg-item-1.jpg',
            ],
        ];

        // 🔥 About text (oddiy string sifatida)

        $about = [
            'subtitle'    => 'Biz haqimizda',
            'title'       => 'PromoBank - Sizning ishonchli hamkoringiz!',
            'description' => 'PromoBank - bu innovatsion yechimlar va yuqori sifatli xizmatlar bilan mijozlarimizni qo‘llab-quvvatlaydigan ishonchli hamkor. Bizning maqsadimiz — mijozlarimizning ehtiyojlarini qondirish va ularga eng yaxshi tajribani taqdim etishdir.',
            'image'       => 'assets/image/sponsors/1.png',
            'list'       => [
                'Innovatsion yechimlar',
                'Yuqori sifatli xizmatlar',
                'Ishonchli hamkorlik',
                'Moslashuvchan platforma',
                'Barqaror sheriklik',
                'Mijozlarga sodiqlik',
            ],
        ];

        return view('frontend.home', compact(
            'socialLinks',
            'contacts',
            'heroTitle',
            'download',
            'promos',
            'benefits',
            'portfolios',
            'forSponsors',
            'sponsors',
            'about',
        ));
    }
}
