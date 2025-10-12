<?php

namespace App\Http\Controllers\WebApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromotionsController extends Controller
{

    protected $url, $promo;
    public function __construct()
    {
        $this->url = config('services.urls.web_service');
        $this->promo = config('services.urls.promo_service');
    }
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $request->merge(['lang' => $locale]);
        $mainResponse = $this->forwardRequest("POST", $this->url, "frontend/pages", $request);
        $promoResponse = $this->forwardRequest("POST", $this->promo, "frontend/", $request);
        if (
            $mainResponse instanceof \Illuminate\Http\Client\Response
            && $promoResponse instanceof \Illuminate\Http\Client\Response
        ) {
            $mainData = $mainResponse->json() ?? [];
            $promoData = $promoResponse->json() ?? [];
            $mergedData = array_merge($mainData, [
                'promos' => $promoData['data'] ?? $promoData
            ]);
            // dd($mergedData);
            return view('webapp.promotions.index', $mergedData);
        }
        return response()->json(['message' => 'Service error'], 500);
    }
    public function show(Request $request, $id)
    {
        $locale = app()->getLocale();
        $request->merge(['lang' => $locale]);

        try {
            $mainResponse = $this->forwardRequest("POST", $this->url, "frontend/pages", $request);
            $promotionResponse = $this->forwardRequest("POST", $this->promo, "frontend/promotion/{$id}", $request);
            // Xizmat javobini tekshirish
            if (!$mainResponse instanceof \Illuminate\Http\Client\Response || !$promotionResponse instanceof \Illuminate\Http\Client\Response) {
                return view('frontend.error', [
                    'status' => 500,
                    'message' => 'Xizmat bilan aloqa o‘rnatilmadi.'
                ]);
            }

            if ($mainResponse->failed()) {
                return view('frontend.error', [
                    'status' => $mainResponse->status(),
                    'message' => $mainResponse->json('message') ?? 'Asosiy sahifa maʼlumotlarini olishda xatolik.'
                ]);
            }

            if ($promotionResponse->failed()) {
                return view('frontend.error', [
                    'status' => $promotionResponse->status(),
                    'message' => $promotionResponse->json('message') ?? 'Aksiya maʼlumotlarini olishda xatolik.'
                ]);
            }

            $mainData = $mainResponse->json() ?? [];
            $promotionData = $promotionResponse->json() ?? [];

            // Agar aksiya yo‘q bo‘lsa (backend 404 qaytargan bo‘lsa)
            if ($promotionResponse->status() == 404) {
                abort(404);
            }

            $mergedData = array_merge($mainData, [
                'promotion' => $promotionData['data'] ?? $promotionData
            ]);

            return view('webapp.promotions.show', $mergedData);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return abort(404, 'Bunday aksiya topilmadi.');
        } catch (\Throwable $e) {
            abort(500); // Laravel default 500 sahifasi
        }
    }
    public function verifyPromo(Request $request, $id)
    {
        $locale = app()->getLocale();
        $request->merge(['lang' => $locale]);
        $response = $this->forwardRequest("POST", $this->promo, "frontend/promotion/{$id}/promocode", $request);
        return response()->json($response->json());
    }

    public function verifyReceipt(Request $request, $id)
    {
        $locale = app()->getLocale();
        $request->merge(['lang' => $locale]);
        $response = $this->forwardRequest("POST", $this->promo, "frontend/promotion/{$id}/receipt", $request);
        Log::info("Response",[$response->json()]);
        return response()->json($response->json());
    }
}
