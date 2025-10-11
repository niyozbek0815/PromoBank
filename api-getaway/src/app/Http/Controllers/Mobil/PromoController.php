<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromoController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }

    public  function index(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, '/promotions', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }

    public function viaPromocode(Request $request, $promotionId)
    {
        $response = $this->forwardRequest("POST", $this->url, '/promotions/' . $promotionId . '/participate/promocode', $request);
        Log::info('Log data', ['data' => $response->json()]);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }

    // public function viaReceipt(Request $request, $promotionId)
    // {
    //     $response = $this->forwardRequest("POST", $this->url, '/promotions/'.$promotionId.'/participate/receipt', $request);
    //     if ($response instanceof \Illuminate\Http\Client\Response) {
    //         return response()->json($response->json(), $response->status());
    //     }
    //     return $response;
    // }

    public function checkStatus(Request $request, $promotionId)
    {
        $response = $this->forwardRequest("GET", $this->url, '/promotions/' . $promotionId . '/participation-status', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
    public function listParticipationHistory(Request $request, $promotionId)
    {
        $response = $this->forwardRequest("POST", $this->url, '/promotions/' . $promotionId . '/participations', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
}
