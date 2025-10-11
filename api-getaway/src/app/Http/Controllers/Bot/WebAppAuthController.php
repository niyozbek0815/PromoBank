<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebAppAuthController extends Controller
{

    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.auth_service');
    }

    public function login(Request $request)
    {
        Log::info('WebAppAuthController login called',['request'=>$request->all()]);
        $response = $this->forwardRequest("POST", $this->url, '/webapp/auth', $request);
Log::info('WebAppAuthController login response',['response'=>$response->json()]);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
    public function refresh(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, '/auth/refresh', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
    public function logout(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, '/auth/logout', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }

}
