<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromotionShowWebResource;
use App\Models\EncouragementPoint;
use App\Models\PromotionProgressBar;
use App\Repositories\PromotionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PromotionController extends Controller
{
    public function __construct(
        private PromotionRepository $promotionRepository,
    ) {}

    public function show(Request $request, $id)
    {
        $lang = $request->get('lang', 'uz'); // Default til 'uz'
        $cacheKey = "promotions:platform:website:show:{$id}:lang:{$lang}";
        $ttl      = now()->addMinutes(3);
        // Cache::store('redis')->forget($cacheKey);
        $promotion = Cache::store('redis')->remember($cacheKey, $ttl, function () use ($id) {
            return $this->promotionRepository->getAllPromotionsShowForWebHome($id);
        });
        return response()->json(
            (new PromotionShowWebResource($promotion))->additional(['lang' => $lang])
        );
    }


}
