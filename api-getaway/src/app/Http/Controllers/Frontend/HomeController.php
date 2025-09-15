<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.web_service');
    }
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $request->merge(['lang' => $locale]);

        $response = $this->forwardRequest("POST", $this->url, "frontend/", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            // dd($response->json());
            return view('frontend.home', $response->json());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
}
