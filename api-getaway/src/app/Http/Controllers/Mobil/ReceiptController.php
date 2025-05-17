<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }

    public function index(Request $request)
    {
        return $this->forwardRequest("POST", $this->url, '/receipt', $request);
    }
}
