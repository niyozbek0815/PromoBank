<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{

    protected $gameServices;
    public function __construct()
    {
        $this->gameServices = config('games.services');
    }
    public  function listAllGames(Request $request)
    {

        $results = [];
        foreach ($this->gameServices as $slug => $url) {
            try {
                $response = $this->forwardRequest("POST", $url, '/info', $request);
                if ($response->ok()) {
                    $res = $response->json();
                    $results[] = $res['data'];
                }
            } catch (\Throwable $e) {
                continue;
            }
        }
        return $this->successResponse($results, "success", 200);
    }
    public function rating(Request $request)
    {
        return $this->forwardToPromo($request, '/promoball/game-rating');
    }

    public function myGamePoints(Request $request)
    {
        return $this->forwardToPromo($request, '/promoball/my-game-points');
    }

    /**
     * 🔹 Promo-service’ga so‘rovni jo‘natish uchun umumiy helper
     */
    private function forwardToPromo(Request $request, string $endpoint)
    {
        $promoUrl = config('services.urls.promo_service');
        $response = $this->forwardRequest('POST', $promoUrl, $endpoint, $request);

        return $response->ok()
            ? $response->json()
            : response()->json([
                'success' => false,
                'message' => 'Promo-service bilan aloqa muvaffaqiyatsiz tugadi',
                'status' => $response->status(),
            ], $response->status());
    }

}
