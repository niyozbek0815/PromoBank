<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $url, $promo;
    public function __construct()
    {
        $this->url = config('services.urls.web_service');
        $this->promo= config('services.urls.promo_service');
    }
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $request->merge(['lang' => $locale]);
        $mainResponse = $this->forwardRequest("POST", $this->url, "frontend/", $request);
        $promoResponse = $this->forwardRequest("POST", $this->promo, "frontend/", $request);
        if (
            $mainResponse instanceof \Illuminate\Http\Client\Response
            && $promoResponse instanceof \Illuminate\Http\Client\Response
        ) {
            $mainData  = $mainResponse->json() ?? [];
            $promoData = $promoResponse->json() ?? [];
            $mergedData = array_merge($mainData, [
                'promos' => $promoData['data'] ?? $promoData
            ]);
            // dd($mergedData);
            return view('frontend.home', $mergedData);
        }
        return response()->json(['message' => 'Service error'], 500);
    }
}
