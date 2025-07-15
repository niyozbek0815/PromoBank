<?php
namespace App\Http\Controllers\Admin\Promo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromotionController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }
    public function index()
    {
        return view('admin.promotion.index');
    }

    public function create(Request $request)
    {
        // dd($company_id = $request->query('company_id'));
        $response = $this->forwardRequest("GET", $this->url, "front/promotion/create", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            $companies         = $response->json('companies');
            $platforms         = $response->json('platforms');
            $partisipants_type = $response->json('partisipants_type');
            $selectedCompanyId = $request->query('company_id');

            // dd($companies);
            return view('admin.promotion.create', compact(var_name: ['companies', 'platforms', 'partisipants_type', 'selectedCompanyId']));
        }

    }
    public function companydata(Request $request, $id)
    {
        $locale = app()->getLocale();
        $request->merge(['locale' => $locale]);
        $response = $this->forwardRequest("GET", $this->url, 'front/promotion/' . $id . '/data', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }

    public function changeStatus(Request $request, $id)
    {
        $response = $this->forwardRequest("POST", $this->url, "front/promotion/{$id}/status", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function changePublic(Request $request, $id)
    {
        $response = $this->forwardRequest("POST", $this->url, "front/promotion/{$id}/public", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function delete(Request $request, $id)
    {
        Log::info(message: "so'rov keldi delete");

        $response = $this->forwardRequest("POST", $this->url, "front/promotion/{$id}/delete", $request);
        if ($response->ok()) {
            return redirect()->back()->with('success', 'Promo aksiya o‘chirildi.');
        }
        return redirect()->back()->with('error', 'Promo aksiyani o‘chirishda xatolik.');
    }
    public function data(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, 'front/promotion/data', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }

    public function edit(Request $request, $id)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/promotion/{$id}/edit", $request);

        if ($response->ok()) {
            $data = $response->json();
            dd($data);
            // return view('admin.users.edit', compact('data'));
        }
        return redirect()->back()->with('error', 'Foydalanuvchi topilmadi.');
    }
    public function store(Request $request)
    {
        $response = $this->forwardRequestMedias(
            'POST',
            $this->url,
            'front/promotion',
            $request,
            ['media_preview', 'media_gallery', 'offer_file']// Fayl nomlari (formdagi `name=""`)
        );
        dd(vars: $response->json());
        if ($response->ok()) {
            return redirect()->route('admin.promotion.index')
                ->with('success', 'Promoaksiya muvaffaqiyatli saqlandi.');
        }
        if ($response->status() === 422) {
            return redirect()->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        Log::error('Promo saqlashda xatolik', ['response' => $response->body()]);
        return redirect()->back()->with('error', 'Promoaksiya saqlanmadi.');
    }

}