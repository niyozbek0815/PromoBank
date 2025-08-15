<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BannersController extends Controller
{
    protected string $url;

    public function __construct()
    {
        // promo_service URL ni config/services.php ichidan olish
        $this->url = config('services.urls.promo_service');
    }
    public function index(Request $request)
    {
        dd("data");
        return view('admin.promotion_products.index');
    }
    /**
     * Yangi promotion product yaratish formasi
     */
    public function create(Request $request, $promotion_id = null)
    {
        $endpoint = "front/promotion_products/create/{$promotion_id}";
        $response = $this->forwardRequest("GET", $this->url, $endpoint, $request);
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            $data = $response->json();
            return view('admin.promotion_products.create', $data);
        }

        abort(404, 'Xizmatdan maʼlumot olishda xatolik yuz berdi.');
    }
}
