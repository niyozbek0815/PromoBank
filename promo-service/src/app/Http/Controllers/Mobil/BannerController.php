<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{

        public function index()
    {
        $banners = Banner::active()->get()->map(function ($banner) {
            return [
                'title'       => [
                    'uz' => $banner->getTranslation('title', 'uz') ?? null,
                    'ru' => $banner->getTranslation('title', 'ru') ?? null,
                    'kr' => $banner->getTranslation('title', 'kr') ?? null,
                ],
                'media'       => [
                    'uz' => $banner->banners_uz ? [
                        'url'       => $banner->banners_uz['full_url'] ?? $banner->banners_uz['url'],
                        'mime_type' => $banner->banners_uz['mime_type'] ?? null,
                    ] : null,
                    'ru' => $banner->banners_ru ? [
                        'url'       => $banner->banners_ru['full_url'] ?? $banner->banners_ru['url'],
                        'mime_type' => $banner->banners_ru['mime_type'] ?? null,
                    ] : null,
                    'kr' => $banner->banners_kr ? [
                        'url'       => $banner->banners_kr['full_url'] ?? $banner->banners_kr['url'],
                        'mime_type' => $banner->banners_kr['mime_type'] ?? null,
                    ] : null,
                ],
                'url'         => $banner->url,
                'banner_type' => $banner->banner_type,
            ];
        });

        return response()->json($banners);
    }
}
