<?php

namespace App\Http\Controllers\Mobil;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{

    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.auth_service');
    }
    public function guest(Request $request)
    {
        return $this->forwardRequest("POST", $this->url, '/guest-login', $request);
    }
    public function login(Request $request)
    {
        return $this->forwardRequest("POST", $this->url, '/login', $request);
    }

    public function register(Request $request)
    {

        return $this->forwardRequest("POST", $this->url, '/register', $request);
    }
    public function check(Request $request, $id)
    {
        return $this->forwardRequest("POST", $this->url, '/verifications/' . $id, $request);
    }
    public function userupdate(Request $request)
    {
        return $this->forwardRequest("PUT", $this->url, '/me', $request);
    }
    public function checkUpdate(Request $request)
    {
        return $this->forwardRequest("POST", $this->url, '/me/verify-update', $request);
    }

    public function user(Request $request)
    {
        return $this->forwardRequest("GET", $this->url, '/me', $request);
    }
    // public function logout(Request $request)
    // {
    //     return $this->forwardRequest("DELETE", $this->url, '/logout', $request);
    // }
}
