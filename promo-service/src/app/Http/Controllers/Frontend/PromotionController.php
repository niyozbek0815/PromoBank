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
    public function rating(Request $request, $id)
    {

   
        $user = $request['auth_user'];

        $dayStartAt = Cache::remember("promotion:progressbar:daystart:$id", now()->addMinutes(15), function () use ($id) {
            return PromotionProgressBar::where('promotion_id', $id)->value('day_start_at');
        }) ?? '00:00';

        [$hour, $minute] = explode(':', $dayStartAt);
        $now = now();
        // $start = $now->copy()->setTime($hour, $minute);
        // if ($now->lt($start)) {
        //     $start->subDay();
        // }
        // $end = $start->copy()->addDay();
        $start = $now->copy()->subWeek(); // bir hafta oldin
        $start->setTime($hour, $minute);   // start vaqtini dayStartAt ga sozlash

        $end = $now->copy();
        $start = $start->copy()->subHours(5);
        $end = $end->copy()->subHours(5);
        // Foydalanuvchi ballarini faqat shu oraliqda hisoblaymiz
        $usersPoints = EncouragementPoint::getUserTotalAndRank(
            $user['id'],
            ['referral_start', 'referral_register', 'secret_number'],
            "Noma'lum user",
            $start,
            $end
        );
        
        $topUsers = EncouragementPoint::getTopUsersWithRank(
            ['referral_start', 'referral_register', 'secret_number'],
            $start,
            $end,
            ($usersPoints && $usersPoints['rank'] > 100) ? 99 : 100
        );
        return response()->json([
            'refresh_time' =>$dayStartAt,
            'range' => [
                'from' => $start->toDateTimeString(),
                'to' => $end->toDateTimeString(),
            ],
            'user_info' => $usersPoints,
            'data' => $topUsers,
        ]);
    }

}
