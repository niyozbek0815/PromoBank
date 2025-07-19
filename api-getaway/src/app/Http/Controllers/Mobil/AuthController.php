<?php
namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.auth_service');
    }
    public function guest(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, '/guest-login', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
    public function login(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, '/login', $request);

        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }

    public function register(Request $request)
    {

        $response = $this->forwardRequest("POST", $this->url, '/register', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
    public function check(Request $request, $id)
    {
        $response = $this->forwardRequest("POST", $this->url, '/verifications/' . $id, $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
    public function userupdate(Request $request)
    {
        $response = $this->forwardRequest("PUT", $this->url, '/me', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
    public function checkUpdate(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, '/me/verify-update', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }

    public function user(Request $request)
    {
        Log::info("Me ishladi");

        $response = $this->forwardRequest("GET", $this->url, '/me', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
    // public function logout(Request $request)
    // {
    //     return $this->forwardRequest("DELETE", $this->url, '/logout', $request);
    // }
}