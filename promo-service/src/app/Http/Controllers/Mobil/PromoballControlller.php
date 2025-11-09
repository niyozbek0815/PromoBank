<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Models\EncouragementPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromoballControlller extends Controller
{
    public function gameRating(Request $request)
    {
        $user = $request['auth_user'];
        $userId = $user['id'];

        // 🔹 Umumiy reyting (faqat game turlarini hisoblaymiz)
        $ranked = EncouragementPoint::query()
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->where('type', 'game')
            ->groupBy('user_id')
            ->orderByDesc('total_points');

        // 🔹 Top 100 foydalanuvchilar (Eloquent + with)
        $top100 = $ranked
            ->with('user:user_id,name') // faqat kerakli ustunlar
            ->take(100)
            ->get()
            ->map(function ($item, $index) {
                return [
                    'name' => $item->user?->name ?? 'Noma’lum foydalanuvchi',
                    'total_points' => (int) $item->total_points,
                    'rank' => $index + 1,
                ];
            });

        // 🔹 Hozirgi foydalanuvchi
        $userStats = EncouragementPoint::query()
            ->select('user_id', DB::raw('SUM(points) as total_points'))
            ->where('type', 'game')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->get();

        $currentUser = $userStats
            ->values()
            ->map(function ($item, $index) {
                $item->rank = $index + 1;
                return $item;
            })
            ->firstWhere('user_id', $userId);

        $currentUserData = null;
        if ($currentUser) {
            $userModel = \App\Models\UsersCache::where('user_id', $userId)->first();
            $currentUserData = [
                'name' => $userModel?->name ?? 'Noma’lum foydalanuvchi',
                'total_points' => (int) $currentUser->total_points,
                'rank' => $currentUser->rank,
            ];
        }
        return $this->successResponse(
            [
                'top_100' => $top100,
                'current_user' => $currentUserData,
            ],
            'success'
        );

  }
    public function myGamePoints(Request $request)
    {
        $user = $request['auth_user'];
        $userId = $user['id'];

        $points = \App\Models\EncouragementPoint::query()
            ->where('user_id', $userId)
            ->where('type', 'game')
            ->sum('points');
        return $this->successResponse(
            [
                'name' => $user['name'] ?? 'Noma’lum foydalanuvchi',
                'total_points' => (int) $points,
            ],
            'success'
        );
    }
}
