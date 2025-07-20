<?php
namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $banners = [
            [
                'title'       => [
                    'uz' => 'Yozgi aksiya boshlandi!',
                    'ru' => 'Летняя акция началась!',
                    'kr' => 'Йозги акция бошланди!',
                ],
                'media'       => [
                    'uz' => 'https: //www.pinterest.com/pin/103231016456382347/',
                    'ru' => 'https: //www.pinterest.com/pin/103231016456382347/',
                    'kr' => 'https: //www.pinterest.com/pin/103231016456382347/',
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
                    'uz' => 'https: //www.pinterest.com/pin/126171227056001059/',
                    'ru' => 'https: //www.pinterest.com/pin/126171227056001059/',
                    'kr' => 'https: //www.pinterest.com/pin/126171227056001059/',
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
                    'uz' => 'https: //www.pinterest.com/pin/25473554137498545/',
                    'ru' => 'https: //www.pinterest.com/pin/25473554137498545/',
                    'kr' => 'https: //www.pinterest.com/pin/25473554137498545/',
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
                    'uz' => 'https: //www.pinterest.com/pin/370984088067376830/',
                    'ru' => 'https: //www.pinterest.com/pin/370984088067376830/',
                    'kr' => 'https: //www.pinterest.com/pin/370984088067376830/',
                ],
                'url'         => '4',
                'banner_type' => 'promotion',
            ],
        ];
return $this->successResponse( $banners);
    }
}
