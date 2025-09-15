<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\About;
use App\Models\Benefit;
use App\Models\Contact;
use App\Models\Download;
use App\Models\ForSponsor;
use App\Models\Portfolio;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->get('lang', 'uz'); // Default til 'uz'

        // About ma'lumotini olish va formatlash
        $aboutModel = About::first();
        $about = $aboutModel ? [
            'subtitle'    => $aboutModel->subtitle[$lang] ?? '',
            'title'       => $aboutModel->title[$lang] ?? '',
            'description' => $aboutModel->description[$lang] ?? '',
            'image'       => $aboutModel->image ?? '',
            'list'        => $aboutModel->list[$lang] ?? [],
        ] : null;

        // Download ma'lumotini olish va formatlash
        $downloadModel = Download::with('links')->first();
        $download = $downloadModel ? [
            'subtitle'    => $downloadModel->subtitle[$lang] ?? '',
            'title'       => $downloadModel->title[$lang] ?? '',
            'description' => $downloadModel->description[$lang] ?? '',
            'image'       => $downloadModel->image ?? '',
            'links'       => $downloadModel->links->map(fn($link) => [
                'type' => $link->type,
                'url'  => $link->url,
            ])->values()->toArray(),
        ] : null;

        $socialLinks=  SocialLink::where('status', 1)->where('status', 1)
            ->orderBy('position')
            ->get(['type', 'url'])
            ->map(fn($link) => [
                'type' => $link->type,
                'url'  => $link->url,
            ])
            ->toArray();


        $contacts = Contact::where('status', 1)
            ->orderBy('position')
            ->get(['type', 'url', 'label'])
            ->map(fn($contact) => [
                'type'  => $contact->type,
                'url'   => $contact->url,
                'label' => $contact->getTranslation('label', $lang),
            ])
            ->toArray();


        $benefits = Benefit::orderBy('position', 'asc')
            ->take(6)
            ->get()
            ->mapWithKeys(function ($benefit, $index) use ($lang) {
                return [
                    $index + 1 => [
                        'title' => $benefit->getTranslation('title', $lang),
                        'desc'  => $benefit->getTranslation('description', $lang),
                        'img'   => $benefit->image,
                    ]
                ];
            })
            ->toArray();
        $sponsors = Sponsor::where('status', 1)
            ->orderBy('weight', 'asc')
            ->get()
            ->map(function ($sponsor, $index) use ($lang) {
                return [
                    'url' => $sponsor->url,
                    'img' => $sponsor->image,
                    'alt' => $sponsor->getTranslation('name', $lang) ?: 'Sponsor ' . ($index + 1),
                ];
            })
            ->values()  // index 0,1,2,... qilib beradi
            ->toArray();
        $forSponsors = ForSponsor::where('status', 1)
            ->orderBy('position', 'asc') // position bo‘yicha tartiblash
            ->take(8)                     // faqat 8 ta yozuv
            ->get()
            ->map(function ($item) use ($lang) {
                return [
                    'title' => $item->getTranslation('title', $lang),
                    'desc'  => $item->getTranslation('description', $lang),
                    'img'   => $item->image,
                ];
            })
            ->values();



        $portfolios = Portfolio::where('status', 1)
            ->orderBy('position', 'asc')
            ->get()
            ->map(function ($item) use ($lang) {
                return [
                    'title'    => $item->getTranslation('title', $lang),
                    'subtitle' => $item->getTranslation('subtitle', $lang),
                    'img'      => $item->image,
                ];
            })
            ->values();
        $settings = Setting::all()->mapWithKeys(function ($setting) use ($lang) {
            $val = $setting->val;
            if (is_string($val)) {
                $decoded = json_decode($val, true);
                if ($decoded !== null) {
                    $val = $decoded;
                }
            }
            if (is_array($val) && $setting->key_name !== 'languages') {
                $val = $val[$lang] ?? reset($val);
            }
            return [$setting->key_name => $val];
        });
        $promos = [
            ['title' => 'Beeline — "1 oy bepul internet" aksiyasi', 'img' => 'assets/image/promotion/1.gif'],
            ['title' => 'Artel — "Yozgi chegirmalar marafoni 20%"', 'img' => 'assets/image/promotion/13e5cd704f9ed67fc562481be03db292.jpg'],
            ['title' => 'Coca-Cola Uzbekistan — "Qadoqni ochib yut!"', 'img' => 'assets/image/promotion/3cde50d623fc002fcab5bfba3bc72743.jpg'],
            ['title' => 'Pepsi Uzbekistan — "1ta sotib ol, 2chisi sovg‘a"', 'img' => 'assets/image/promotion/8817b552c2d5ff638921e8c6fdf9280d.jpg'],
            ['title' => 'Carrefour — "Hafta oxiri maxsus chegirmalar"', 'img' => 'assets/image/promotion/_.jpeg'],
            ['title' => 'Ucell — "10GB bonus internet" lotereyasi', 'img' => 'assets/image/promotion/winter_exclusive_for_man-cover.png'],
            ['title' => 'Korzinka — "Black Friday 50% gacha chegirma"', 'img' => 'assets/image/promotion/Modelo de banner preto de venda na sexta-feira com sacos de loja em 3D e cubos flutuando até o carrinho de compras _ PSD Premium.jpeg'],
        ];

        return response()->json([
            'socialLinks' => $socialLinks,
            'contacts'    => $contacts,
            'download'    => $download,
            'promos'      => $promos,
            'benefits'    => $benefits,
            'portfolios'  => $portfolios,
            'forSponsors' => $forSponsors,
            'sponsors'    => $sponsors,
            'about'       => $about,
            'settings'=>$settings
        ]);
    }
}
