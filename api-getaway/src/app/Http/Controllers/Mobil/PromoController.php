<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }

    public  function index(Request $request)
    {
        return $this->forwardRequest("GET", $this->url, '/promotions', $request);
    }

    public function viaPromocode(Request $request, $promotionId)
    {
        return $this->forwardRequest("POST", $this->url, '/promotions/' . $promotionId . '/participate/promocode', $request);
    }

    public function viaReceipt(Request $request, $promotionId)
    {
        return $this->forwardRequest("POST", $this->url, '/promotions/' . $promotionId . '/participate/receipt', $request);
    }

    public function checkStatus(Request $request, $promotionId)
    {
        return $this->forwardRequest("GET", $this->url, '/promotions/' . $promotionId . '/participation-status', $request);
    }
}
