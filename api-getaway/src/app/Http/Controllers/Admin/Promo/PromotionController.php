<?php
namespace App\Http\Controllers\Admin\Promo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        $response = $this->forwardRequest("GET", $this->url, "front/promotion/create", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            $companies         = $response->json('companies');
            $platforms         = $response->json('platforms');
            $partisipants_type = $response->json('partisipants_type');
            $selectedCompanyId = $request->query('company_id');
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
                return view('admin.promotion.edit', [
                'promotion'         => $data['promotion'],
                'platforms'         => $data['platforms'],         // id => name format
                'partisipants_type' => $data['partisipants_type'], // id => name format
                'companies'         => $data['companies'],
                'prizeCategories'=>$data['prizeCategories'],
            ]);
        }
        return redirect()->back()->with('error', 'Promoaksiya topilmadi.');
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
        if ($response->ok()) {
            return redirect()->route('admin.promotion.index')
                ->with('success', 'Promoaksiya muvaffaqiyatli saqlandi.');
        }
        if ($response->status() === 422) {
            return redirect()->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        return redirect()->back()->with('error', 'Promoaksiya saqlanmadi.');
    }
    public function update(Request $request, $id)
    {
        $response = $this->forwardRequestMedias(
            'POST', // PUT emas, chunki multipart so‘rovda faqat POST ishlaydi
            $this->url,
            "front/promotion/{$id}", // yangilanayotgan promo
            $request,
            ['media_preview', 'media_gallery', 'offer_file']
        );
        if ($response->ok()) {
            return redirect()->route('admin.promotion.index')
                ->with('success', 'Promoaksiya muvaffaqiyatli yangilandi.');
        }

        if ($response->status() === 422) {
            return redirect()->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }

        return redirect()->back()->with('error', 'Promoaksiya yangilanmadi.');
    }
    public function updateParticipantType(Request $request, $promotionId, $participantTypeId)
    {
        $response = $this->forwardRequestMedias(
            'POST',     // multipart uchun faqat POST ishlaydi
            $this->url, // promo-service yoki media-service URL
            "front/promotion/{$promotionId}/participant-type/{$participantTypeId}/update",
            $request,
            []// bu yerda file yo‘q, faqat is_enabled + additional_rules keladi
        );
        if ($response->ok()) {

            return redirect()->back()->with('success', 'Ishtirok turi muvaffaqiyatli yangilandi.');
        }
        if ($response->status() === 422) {
            return redirect()->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        return redirect()->back()->with('error', 'Ishtirok turini yangilashda xatolik yuz berdi.');
    }
    public function updatePlatform(Request $request, $promotionId, $platformId)
    {
        $response = $this->forwardRequestMedias(
            'POST',     // multipart uchun faqat POST ishlaydi
            $this->url, // promo-service yoki media-service URL
            "front/promotion/{$promotionId}/platform/{$platformId}/update",
            $request,
            []// bu yerda file yo‘q, faqat is_enabled + additional_rules keladi
        );
        if ($response->ok()) {
            return redirect()->back()->with('success', 'Platform muvaffaqiyatli yangilandi.');
        }
        if ($response->status() === 422) {
            return redirect()->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        return redirect()->back()->with('error', 'Platformni yangilashda xatolik yuz berdi.');
    }



}
