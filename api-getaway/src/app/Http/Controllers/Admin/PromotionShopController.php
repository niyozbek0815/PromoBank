<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromotionShopController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }
    public function index(Request $request)
    {
        return view('admin.promotion_shop.index');
    }
    public function create(Request $request, $promotion_id = null)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/promotion_shops/create/{$promotion_id}", $request);
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
            $data = $response->json();
            // dd($data);
            return view('admin.promotion_shop.create', $data);
        }
        abort(404, 'Xizmatdan maʼlumot olishda xatolik yuz berdi.');
    }

    public function store(Request $request)
    {
        $response = $this->forwardRequest(
            'POST',
            $this->url,
            "front/promotion_shops/store",
            $request
        );
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
            return redirect()
                ->back()
                ->with('success', "Do‘kon muvaffaqiyatli qo‘shildi.");
        }
        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }
    public function promotiondata(Request $request, $promotionId)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/promotion_shops/{$promotionId}/promotion_data", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function edit(Request $request, $id)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/promotion_shops/{$id}/edit", $request);
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
            return view('admin.promotion_shop.edit', $response->json());
        }
        abort(404, 'Xizmatdan maʼlumot olishda xatolik yuz berdi.');
    }
    public function update(Request $request, $id)
    {
        $response = $this->forwardRequest(
            'PUT',
            $this->url,
            "front/promotion_shops/{$id}",
            $request
        );
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
            return redirect()
                ->back()
                ->with('success', 'Do‘kon ma’lumotlari muvaffaqiyatli yangilandi.');
        }

        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }
}
