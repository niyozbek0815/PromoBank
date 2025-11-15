<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected $url, $web;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
        $this->web = config('services.urls.web_service');
    }

    public function show(Request $request, $id)
    {
        $locale = app()->getLocale();
        $request->merge(['lang' => $locale]);

        try {
            $mainResponse = $this->forwardRequest("POST", $this->web, "frontend/pages", $request);
            $promotionResponse = $this->forwardRequest("POST", $this->url, "frontend/promotion/{$id}", $request);
            // dd($promotionResponse->json());
            // Xizmat javobini tekshirish
            if (! $mainResponse instanceof \Illuminate\Http\Client\Response || ! $promotionResponse instanceof \Illuminate\Http\Client\Response) {
                return view('frontend.error', [
                    'status'  => 500,
                    'message' => 'Xizmat bilan aloqa o‘rnatilmadi.'
                ]);
            }

            if ($mainResponse->failed()) {
                return view('frontend.error', [
                    'status'  => $mainResponse->status(),
                    'message' => $mainResponse->json('message') ?? 'Asosiy sahifa maʼlumotlarini olishda xatolik.'
                ]);
            }

            if ($promotionResponse->failed()) {
                return view('frontend.error', [
                    'status'  => $promotionResponse->status(),
                    'message' => $promotionResponse->json('message') ?? 'Aksiya maʼlumotlarini olishda xatolik.'
                ]);
            }

            $mainData      = $mainResponse->json() ?? [];
            $promotionData = $promotionResponse->json() ?? [];

            // Agar aksiya yo‘q bo‘lsa (backend 404 qaytargan bo‘lsa)
            if ($promotionResponse->status() == 404) {
                abort(404);
            }

            $mergedData = array_merge($mainData, [
                'promotion' => $promotionData['data'] ?? $promotionData
            ]);

            return view('frontend.promo', $mergedData);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return abort(404, 'Bunday aksiya topilmadi.');
        } catch (\Throwable $e) {
            abort(500); // Laravel default 500 sahifasi
        }
    }
}
