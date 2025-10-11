<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }

    public function index(Request $request)
    {
       $response=  $this->forwardRequest("POST", $this->url, '/receipt', $request);
        Log::info("data1",['data'=>$response->json()]);
              if ($response instanceof \Illuminate\Http\Client\Response) {
                   return response()->json($response->json());
              }
    }
    public function points(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, '/receipt/user_points', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json());
        }
    }
}
