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

        return $this->forwardRequest("GET", $this->url, '/regions', $request);
    }

    public function district(Request $request, $id)
    {
        return $this->forwardRequest("GET", $this->url, '/regions/' . $id, $request);
    }
}
