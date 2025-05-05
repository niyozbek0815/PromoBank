<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }

    public  function index(Request $request)
    {
        return $this->forwardRequest("GET", $this->url, '/promotions', $request);
    }

    public function viaPromocode(Request $request, $promotionId)
    {
        $request->validate([
            'code' => 'required|string',
            'type' => 'in:text,qr' // optional: distinguish input type
        ]);

        // Logika: promokodni tekshirish va ishtirokni yozish
    }

    public function viaReceipt(Request $request, $promotionId)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpeg,png,pdf', // yoki base64
        ]);

        // Logika: checkni yuklash va tekshirish
    }

    public function checkStatus($promotionId)
    {
        // Logika: foydalanuvchi allaqachon ishtirok etganmi tekshirish
    }
}
