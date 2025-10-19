<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromotionProductController extends Controller
{
    protected string $url;

    public function __construct()
    {
        // promo_service URL ni config/services.php ichidan olish
        $this->url = config('services.urls.promo_service');
    }
    public function index(Request $request)
    {
        return view('admin.promotion_products.index');
    }
    public function create(Request $request, $promotion_id = null)
    {
        $endpoint = "front/promotion_products/create/{$promotion_id}";
        $response = $this->forwardRequest("GET", $this->url, $endpoint, $request);
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
            $data = $response->json();
            return view('admin.promotion_products.create', $data);
        }
        abort(404, 'Xizmatdan maʼlumot olishda xatolik yuz berdi.');
    }
    public function store(Request $request)
    {
        $endpoint = "front/promotion_products/store";
        $response = $this->forwardRequest("POST", $this->url, $endpoint, $request);
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
            return redirect()
                ->back()
                ->with('success', "Mahsulot muvaffaqiyatli qo‘shildi.");
        }
        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }

    public function data(Request $request)
    {
        $endpoint = "front/promotion_products/data";
        $response = $this->forwardRequest("GET", $this->url, $endpoint, $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function promotiondata(Request $request, $promotionId)
    {
        $endpoint = "front/promotion_products/{$promotionId}/promotion_data";
        $response = $this->forwardRequest("GET", $this->url, $endpoint, $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function edit(Request $request, $id)
    {
        $endpoint = "front/promotion_products/{$id}/edit";
        $response = $this->forwardRequest("GET", $this->url, $endpoint, $request);
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
            return view('admin.promotion_products.edit', $response->json());
        }
        abort(404, 'Xizmatdan maʼlumot olishda xatolik yuz berdi.');
    }
    public function update(Request $request, $id)
    {
        $endpoint = "front/promotion_products/{$id}";
        $response = $this->forwardRequest("PUT", $this->url, $endpoint, $request);
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
            return redirect()
                ->back()
                ->with('success', 'Mahsulot ma’lumotlari muvaffaqiyatli yangilandi.');
        }
        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }
}
