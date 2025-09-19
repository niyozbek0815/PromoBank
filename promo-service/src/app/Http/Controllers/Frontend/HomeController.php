<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromoWebResource;
use App\Services\ViaPromocodeService;
use App\Repositories\PromotionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct(
        private ViaPromocodeService $viaPromocodeService,
        private PromotionRepository $promotionRepository,
    ) {
        $this->viaPromocodeService = $viaPromocodeService;
        $this->promotionRepository = $promotionRepository;
    }

    public function index(Request $request)
    {
        $lang = $request->get('lang', 'uz'); // Default til 'uz'

        $cacheKey = 'promotions:platform:website:page:' . request('page', 1);
        $ttl      = now()->addMinutes(3); // 5 daqiqa kesh
        Cache::store('redis')->forget($cacheKey);

        $promotions = Cache::store('redis')->remember($cacheKey, $ttl, function () {
            return $this->promotionRepository->getAllPromotionsForWebHome();
        });
        $lang = $request->get('lang', 'uz'); // Default til 'uz'

        return response()->json(data: PromoWebResource::collection($promotions)->additional(['lang' => $lang]));
    }

}
