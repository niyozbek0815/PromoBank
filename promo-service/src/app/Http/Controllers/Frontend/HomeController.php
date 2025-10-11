<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromoWebResource;
use App\Repositories\PromotionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct(
        private PromotionRepository $promotionRepository,
    ) {
    }

    public function index(Request $request)
    {
        $lang = $request->get('lang', 'uz');
        $page = $request->get('page', 1);
        $cacheKey = "promotions:platform:website:lang:{$lang}:page:{$page}";
        $ttl = now()->addMinutes(3);
        $promotions = Cache::store('redis')->remember($cacheKey, $ttl, function () {
            return $this->promotionRepository->getAllPromotionsForWebHome();
        });
        return response()->json(
            PromoWebResource::collection($promotions)->additional(['lang' => $lang])
        );
    }

}
