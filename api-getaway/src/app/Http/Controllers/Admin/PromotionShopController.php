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
        return view('admin.reciepts.index');
    }
    public function create(Request $request, $promotion_id = null)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/promotion_shops/create/{$promotion_id}", $request);
        // dd( $response->json());
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            $data = $response->json();
            // dd($data);
            return view('admin.reciepts.create', $data);
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
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return redirect()
                ->back()
                ->with('success', "Do‘kon muvaffaqiyatli qo‘shildi.");
        }
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());

        return redirect()
            ->route('admin.promotion_shops.index')
            ->with('success', 'Do‘kon muvaffaqiyatli qo‘shildi.');
    }
      public function promotiondata(Request $request, $promotionId)
    {
        Log::info("Fetching promotion shop data for promotion ID: {$promotionId}");
        $response = $this->forwardRequest("GET", $this->url, "front/promotion_shops/{$promotionId}/promotion_data", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            Log::info('Promotion shop data fetched successfully', $response->json());
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function edit(Request $request, $id)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/promotion_shops/{$id}/edit", $request);
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
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
        // dd($response->json());
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return redirect()
                ->back()
                ->with('success', 'Do‘kon ma’lumotlari muvaffaqiyatli yangilandi.');
        }
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }
}
