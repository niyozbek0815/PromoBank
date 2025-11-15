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
        $promoResponse = $this->forwardRequest("POST", $this->promo, "webapp/promotions", $request);
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
            $promotionResponse = $this->forwardRequest("POST", $this->promo, "webapp/promotions/{$id}", $request);
            // dd($promotionResponse->json());
            // Xizmat javobini tekshirish
            if (!$mainResponse instanceof \Illuminate\Http\Client\Response || !$promotionResponse instanceof \Illuminate\Http\Client\Response) {
                abort(500, 'Xizmat bilan aloqa o‘rnatilmadi.');
            }

            if ($mainResponse->failed()) {
                abort($mainResponse->status(), $mainResponse->json('message') ?? 'Asosiy sahifa maʼlumotlarini olishda xatolik.');
            }

            if ($promotionResponse->failed()) {
                abort($promotionResponse->status(), $promotionResponse->json('message') ?? 'Aksiya maʼlumotlarini olishda xatolik.');
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
        $locale = $request->header('X-Locale') ?? session('locale', config('app.locale'));
        $request->merge(['lang' => $locale]);
        $response = $this->forwardRequest("POST", $this->promo, "webapp/promotions/{$id}/promocode", $request);
        return response()->json($response->json());
    }

    public function verifyReceipt(Request $request, $id)
    {
        $locale = $request->header('X-Locale') ?? session('locale', config('app.locale'));
        $request->merge(['lang' => $locale]);
        $response = $this->forwardRequest("POST", $this->promo, "webapp/promotions/{$id}/receipt", $request);
        Log::info("Response",[$response->json()]);
        return response()->json($response->json());
    }
    public function secretNumber(Request $request, $id)
    {
        $locale = $request->header('X-Locale') ?? session('locale', config('app.locale'));
        Log::info("lang:" . $locale);
        $request->merge(['lang' => $locale]);
        $response = $this->forwardRequest("POST", $this->promo, "webapp/promotions/{$id}/secret-number", $request);
        Log::info("Response", [$response->json()]);
        return response()->json($response->json(),$response->status());
    }
    public function showAjaxData(Request $request, $id)
    {
        $locale = $request->header('X-Locale') ?? session('locale', config('app.locale'));
        $request->merge(['lang' => $locale]);
        $response = $this->forwardRequest("POST", $this->promo, "webapp/promotions/{$id}/showdata", $request);
        Log::info("ResponseShowData", [$response->json()]);
        return response()->json($response->json(), $response->status());
    }
    public function rating(Request $request, $id)
    {
        $locale = app()->getLocale();
        $request->merge(['lang' => $locale]);

        try {
            // Asosiy sahifa ma'lumotlari
            $mainResponse = $this->forwardRequest("POST", $this->url, "frontend/pages", $request);
            // Reyting ma'lumotlari
            $ratingResponse = $this->forwardRequest("POST", $this->promo, "webapp/promotions/{$id}/rating", $request);
            if (!$mainResponse instanceof \Illuminate\Http\Client\Response || !$ratingResponse instanceof \Illuminate\Http\Client\Response) {
                abort($mainResponse->status(), $mainResponse->json('message') ?? 'Asosiy sahifa maʼlumotlarini olishda xatolik.');
            }

            if ($mainResponse->failed() || $ratingResponse->failed()) {
                abort($ratingResponse->status(), $ratingResponse->json('message') ?? 'Aksiya maʼlumotlarini olishda xatolik.');
            }

            $mainData = $mainResponse->json() ?? [];
            $promotionData = $ratingResponse->json() ?? [];
            // Blade uchun data tayyorlash
            $data = [
                'promotion_id' => $id,
                'refresh_time' => $promotionData['refresh_time'] ?? now()->addMinutes(1)->toDateTimeString(),
                'my_rank' => $promotionData['user_info'] ?? [],
                'users' => $promotionData['data'],
            ];
            // dd($data);
            // Bladega yuborish
            return view('webapp.promotions.rating', array_merge($mainData, $data));

        } catch (\Throwable $e) {
            Log::error("Rating fetch error: " . $e->getMessage());
            abort(500);
        }
    }
}
