<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromoWebResource;
use App\Services\ViaPromocodeService;
use App\Repositories\PromotionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        $lang = $request->get('lang', 'uz'); // Default til
        Log::info("Fetching promotions for language: " . $lang);
         // --- IGNORE ---
        $page = $request->get('page', 1);    // Default 1-sahifa
        $cacheKey = "promotions:platform:website:lang:{$lang}:page:{$page}";
        $ttl = now()->addMinutes(3); // 3 daqiqa cache

        Log::info("Fetching promotions", [
            'lang' => $lang,
            'page' => $page,
            'cache_key' => $cacheKey,
        ]);

        // Har testda cache o‘chirish kerak bo‘lsa:
        // Cache::store('redis')->forget($cacheKey);

        $promotions = Cache::store('redis')->remember($cacheKey, $ttl, function () {
            return $this->promotionRepository->getAllPromotionsForWebHome();
        });

        return response()->json(
            PromoWebResource::collection($promotions)->additional(['lang' => $lang])
        );
    }

}
