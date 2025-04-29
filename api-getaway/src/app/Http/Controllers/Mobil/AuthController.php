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
        return $this->forwardRequest("POST", $this->url, '/guest', $request);
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
        return $this->forwardRequest("POST", $this->url, '/check/' . $id, $request);
    }
    public function userupdate(Request $request)
    {
        return $this->forwardRequest("PUT", $this->url, '/user_update', $request);
    }
    public function checkUpdate(Request $request)
    {
        return $this->forwardRequest("POST", $this->url, '/check_update', $request);
    }
    public function logout(Request $request)
    {
        return $this->forwardRequest("DELETE", $this->url, '/logout', $request);
    }
    public function user(Request $request)
    {
        return $this->forwardRequest("GET", $this->url, '/user', $request);
    }
}
