<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        return view('admin.banners.index');
    }
    /**
     * Yangi banner yaratish formasi
     */
    public function edit(Request $request, $id)
    {
        $serviceUrls = [
            'promotion' => config('services.urls.promo_service'),
            'game'      => config('services.urls.game_service'),
        ];

        $endpoints = [
            'promotion' => 'front/promotion/gettypes',
            'game'      => 'front/games/gettypes',
            'banner'    => 'front/banners/' . $id . '/edit',
        ];

        $promotionUrls = [];
        $gameUrls      = [];
        $bannerData    = "";

        $response = $this->forwardRequest("GET", $serviceUrls['promotion'], $endpoints['promotion'], $request);
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            $promotionUrls = $response->json() ?? [];
        }
        $response2 = $this->forwardRequest("GET", $serviceUrls['promotion'], $endpoints['banner'], $request);
        if ($response2 instanceof \Illuminate\Http\Client\Response  && $response2->successful()) {
            $bannerData = $response2->json() ?? [];
            dd($bannerData);
        }

        $response3 = $this->forwardRequest("GET", $serviceUrls['game'], $endpoints['game'], $request);
        if ($response3 instanceof \Illuminate\Http\Client\Response  && $response3->successful()) {
            $gameUrls = $response3->json() ?? [];
        }
        return view('admin.banners.edit', compact('promotionUrls', 'gameUrls', 'bannerData'));
    }
    public function update(Request $request, $id)
    {
        $response = $this->forwardRequestMedias(
            'PUT',
            $this->url,
            'front/banners/' . $id ,
            $request,
            ['media'] // Fayl nomlari (formdagi `name=""`)
        );

        // dd($response->json());
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return redirect()
                ->route('admin.banners.index')
                ->with('success', "Banners muvaffaqiyatli yangilandi.");
        }

        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }

        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }
    public function create(Request $request)
    {
        // Servis URL lar
        $serviceUrls = [
            'promotion' => config('services.urls.promo_service'),
            'game'      => config('services.urls.game_service'),
        ];

        // Endpointlar
        $endpoints = [
            'promotion' => 'front/promotion/gettypes',
            'game'      => 'front/games/gettypes',
        ];

        $promotionUrls = [];
        $gameUrls      = [];

        $response = $this->forwardRequest("GET", $serviceUrls['promotion'], $endpoints['promotion'], $request);
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            $promotionUrls = $response->json() ?? [];
        }

        // Game
        $response = $this->forwardRequest("GET", $serviceUrls['game'], $endpoints['game'], $request);
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            $gameUrls = $response->json() ?? [];
        }

        // Blade ga ikkita o‘zgaruvchi bilan jo‘natamiz
        return view('admin.banners.create', compact('promotionUrls', 'gameUrls'));
    }
    public function store(Request $request)
    {
        $response = $this->forwardRequestMedias(
            'POST',
            $this->url,
            'front/banners/store',
            $request,
            ['media']// Fayl nomlari (formdagi `name=""`)
        );
        // dd($request->all());

        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return redirect()
                ->route('admin.banners.index')
                ->with('success', "Banners muvaffaqiyatli qo‘shildi.");
        }



        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());

    }
    public function data(Request $request)
    {
        Log::info("Fetching banner data", ['request' => $request->all()]);
        $endpoint = "front/banners/data";
        $response = $this->forwardRequest("GET", $this->url, $endpoint, $request);
        Log::info("Banner data fetched", ['response' => $response->json()]);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }

        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function changeStatus(Request $request, $id)
    {
        $endpoint = "front/banners/{$id}/status";
        $response = $this->forwardRequest("POST", $this->url, $endpoint, $request);
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return response()->json(['success' => true, 'message' => 'Status muvaffaqiyatli yangilandi!']);
        }

        return response()->json(['success' => false, 'message' => 'Statusni yangilashda xatolik!'], $response->status());
    }

    public function destroy(Request $request, $id)
    {
        Log::info("Banner delete request", ['id' => $id]);
        $endpoint = "front/banners/{$id}/delete";
        $response = $this->forwardRequest("POST", $this->url, $endpoint, $request);
        Log::info("Banner delete response", ['id' => $id, 'request' => $response->json()]);

        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return response()->json(['success' => true, 'message' => 'Banner muvaffaqiyatli o‘chirildi!']);
        }

        return response()->json(['success' => false, 'message' => 'Bannerni o‘chirishda xatolik!'], $response->status());
    }

}
