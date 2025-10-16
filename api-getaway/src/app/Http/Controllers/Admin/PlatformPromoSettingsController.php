<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlatformPromoSettingsController extends Controller
{
    protected string $url;

    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }

    /**
     * Promoball sozlamalarini ko‘rish
     */
    public function edit(Request $request)
    {
        $response = $this->forwardRequest(
            method: 'GET',
            baseUrl: $this->url,
            path: 'front/settings/platform-promoball/edit',
            request: $request
        );

        if (!($response instanceof \Illuminate\Http\Client\Response)) {
            return response()->json(['message' => 'Promo service bilan aloqa muvaffaqiyatsiz.'], 500);
        }

        if (!$response->successful()) {
            abort($response->status(), 'Promo service xatosi: ' . $response->body());
        }
        $data = $response->json('settings');
        return view('admin.promoball_settings.index', ['settings'=>$data]);
    }

    /**
     * Promoball sozlamalarini yangilash
     */
    public function update(Request $request, $id)
    {
        $response = $this->forwardRequest(
            method: 'PUT',
            baseUrl: $this->url,
            path: "front/settings/platform-promoball/{$id}",
            request: $request
        );

        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }

        if ($response->successful()) {
            return redirect()
                ->back()
                ->with('success', 'Promoball sozlamalari muvaffaqiyatli yangilandi.');
        }

        abort($response->status(), 'Promo service xatosi: ' . $response->body());
    }
}
