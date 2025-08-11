<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromoCodeController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }
    public function create(Request $request, $id = null)
    {

        $response = $this->forwardRequest(
            "GET",
            $this->url,
            "front/promocode/create/{$id}",
            $request
        );
        if ($response instanceof \Illuminate\Http\Client\Response) {
            $settings = $response->json('settings');
            if ($settings === null) {
                return view('admin.promocode.settings', [
                    'promotion_id' => $id,
                    'success'      => "Iltimos, promocode sozlamalarini to'ldiring va promoaksiya qo'shing",
                ]);
            }
            return view('admin.promocode.create', ['settings' => $settings, 'promotion_id' => $id, 'success' => 'Iltimos promocode generatsiya qilishdan oldin sozlamalarni tekshiring.']);
        }
    }
    public function generatePromoCodes(Request $request, $promotionId)
    {
        $response = $this->forwardRequest("POST", $this->url, "front/promocode/{$promotionId}/generate", $request);
        if ($response->ok()) {
            $data = $response->json();
            return redirect()->back()->with('success', $data["message"]);
        }
        if ($response->status() === 422) {
            return redirect()->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        return redirect()->back()->with('error', 'Generatsiya qilishda xatolik.');
    }
    public function importPromoCodes(Request $request, $promotionId)
    {
        // $response = $this->forwardRequest("POST", $this->url, "front/promocode/{$promotionId}/import", $request);
        $response = $this->forwardRequestMedias(
            'POST',
            $this->url,
            "front/promocode/{$promotionId}/import",
            $request,
            ['file']// Fayl nomlari (formdagi `name=""`)
        );
        if ($response->ok()) {
            $data = $response->json();
            return redirect()->back()->with('success', $data["message"]);
        }

        if ($response->status() === 422) {
            return redirect()->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        return redirect()->back()->with('error', 'Generatsiya qilishda xatolik.');

    }
    public function updatePromocodeSettings(Request $request, $promotionId)
    {
        $response = $this->forwardRequest("POST", $this->url, "front/promocode/{$promotionId}/promocode-settings", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            $settings = $response->json('setting');
            // dd($settings);

            return redirect()
                ->route('admin.promocode.create', ['promotion_id' => $promotionId])
                ->with([
                    'settings' => $settings,
                    'success'  => '✅ Promocode sozlamalari yangilandi.',
                ]);
        }
    }
    public function showPromocodeSettingsForm(Request $request, $promotionId)
    {

        $response = $this->forwardRequest("GET", $this->url, "front/promocode/{$promotionId}/promocode-settings", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            $settings = $response->json('settings');
            // dd($settings);
            return view('admin.promocode.settings', ['settings' => $settings, 'promotion_id' => $promotionId, 'success' => 'Promocode sozlamalarini sozlang']);
        }
    }
    public function generateData(Request $request, $promotionId)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/promocode/{$promotionId}/generatedata", $request);
        Log::info($response);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function generateShow(Request $request, $generateId)
    {
        return view('admin.promocode.showgenerate', ['generate_id' => $generateId]);
    }
    public function generatePromocodeData(Request $request, $generateId)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/promocode/{$generateId}/generate/promocodedata", $request);
        Log::info($response);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function promocodeData(Request $request, $generateId)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/promocode/{$generateId}/promocodedata", $request);
        Log::info($response->json());
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function prizeData(Request $request, $promotionId)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/promocode/{$promotionId}/prizedata", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function searchPromocodes(Request $request, $promotionId)
    {
        $query   = $request->input('q');
        $perPage = $request->input('per_page', 20);

        // Forward qilinadigan endpoint
        $response = $this->forwardRequest(
            "GET",
            $this->url,
            "front/promocode/{$promotionId}/search",
            $request
        );
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
  public function autobindData(Request $request, $prizeId)
    {
        Log::info("Autobind data requested for prize: {$prizeId}");
        $response = $this->forwardRequest("GET", $this->url, "front/promocode/{$prizeId}/autobinddata", $request);
        Log::info("Response from autobind data: ", $response->json());
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
}
