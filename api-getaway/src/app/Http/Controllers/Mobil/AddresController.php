<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddresController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.auth_service');
    }

    public function region(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, '/regions', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }

    public function district(Request $request, $id)
    {
        $response = $this->forwardRequest("GET", $this->url, '/regions/' . $id . '/districts', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
}
