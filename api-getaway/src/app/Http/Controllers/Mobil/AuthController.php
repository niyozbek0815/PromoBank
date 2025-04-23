<?php

namespace App\Http\Controllers\Mobil;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Soap\Url;

class AuthController extends Controller
{

    protected $url;
    public function __construct()
    {
        $this->url = config('services.auth_service.url');
    }

    public function login(Request $request)
    {
        return $this->forwardRequest($this->url, '/login', $request);
    }

    public function guest(Request $request)
    {
        return $this->forwardRequest($this->url, '/guest', $request);
    }

    // public function me(Request $request)
    // {
    //     $token = $request->bearerToken();

    //     if (!$token) {
    //         return response()->json(['error' => 'Token not found'], 401);
    //     }

    //     try {
    //         $response = Http::withToken($token)->get("{$this->authServiceUrl}/me");

    //         if ($response->successful()) {
    //             return response()->json($response->json());
    //         }

    //         return response()->json(['error' => 'Unauthorized'], $response->status());
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Auth service unavailable'], 503);
    //     }
    // }
}
