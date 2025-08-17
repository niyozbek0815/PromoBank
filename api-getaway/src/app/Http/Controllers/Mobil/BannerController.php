<?php
namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    protected $url;

    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }

    public function index(Request $request)
    {
        // 🔹 Promo-service’dan ma’lumot olish
        $response = $this->forwardRequest("GET", $this->url, '/banners', $request);

        // 🔹 Default mock data
        $defaultBanners = [
            [
                'title'       => [
                    'uz' => 'Yozgi aksiya boshlandi!',
                    'ru' => 'Летняя акция началась!',
                    'kr' => 'Йозги акция бошланди!',
                ],
                'media'       => [
                    'uz' => ['url' => 'https://qadarun.com/namuna/1.gif', 'mime_type' => 'image/gif'],
                    'ru' => ['url' => 'https://qadarun.com/namuna/1.gif', 'mime_type' => 'image/gif'],
                    'kr' => ['url' => 'https://qadarun.com/namuna/1.gif', 'mime_type' => 'image/gif'],
                ],
                'url'         => '12',
                'banner_type' => 'promotion',
            ],
            [
                'title'       => [
                    'uz' => 'Yangiliklar bilan tanishing',
                    'ru' => 'Узнайте последние новости',
                    'kr' => 'Янгиликлар билан танишинг',
                ],
                'media'       => [
                    'uz' => ['url' => 'https://qadarun.com/namuna/6.jpeg', 'mime_type' => 'image/jpeg'],
                    'ru' => ['url' => 'https://qadarun.com/namuna/6.jpeg', 'mime_type' => 'image/jpeg'],
                    'kr' => ['url' => 'https://qadarun.com/namuna/6.jpeg', 'mime_type' => 'image/jpeg'],
                ],
                'url'         => '7',
                'banner_type' => 'news',
            ],
            [
                'title'       => [
                    'uz' => 'Bizning rasmiy saytimiz',
                    'ru' => 'Наш официальный сайт',
                    'kr' => 'Бизнинг расмий сайтимиз',
                ],
                'media'       => [
                    'uz' => ['url' => 'https://qadarun.com/namuna/8.jpeg', 'mime_type' => 'image/jpeg'],
                    'ru' => ['url' => 'https://qadarun.com/namuna/8.jpeg', 'mime_type' => 'image/jpeg'],
                    'kr' => ['url' => 'https://qadarun.com/namuna/8.jpeg', 'mime_type' => 'image/jpeg'],
                ],
                'url'         => 'https://officialsite.uz',
                'banner_type' => 'url',
            ],
            [
                'title'       => [
                    'uz' => 'Qishgi chegirmalar!',
                    'ru' => 'Зимние скидки!',
                    'kr' => 'Қишги чегирмалар!',
                ],
                'media'       => [
                    'uz' => ['url' => 'https://qadarun.com/namuna/7.jpeg', 'mime_type' => 'image/jpeg'],
                    'ru' => ['url' => 'https://qadarun.com/namuna/7.jpeg', 'mime_type' => 'image/jpeg'],
                    'kr' => ['url' => 'https://qadarun.com/namuna/7.jpeg', 'mime_type' => 'image/jpeg'],
                ],
                'url'         => '4',
                'banner_type' => 'game',
            ],
        ];

        // 🔹 Agar promo-service’dan ma’lumot kelmasa yoki bo‘sh bo‘lsa → default qaytar
        if (
            ! $response instanceof \Illuminate\Http\Client\Response  ||
            ! $response->ok() ||
            empty($response->json())
        ) {
            return $this->successResponse($defaultBanners);
        }
return $this->successResponse($response->json());

        // 🔹 Aks holda real datani qaytar
    }
}
