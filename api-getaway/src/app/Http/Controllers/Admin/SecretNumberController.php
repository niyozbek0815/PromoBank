<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SecretNumberController extends Controller
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

    public function create(Request $request, $id=null)
    {
        // dd($id);
        $response = $this->forwardRequest("GET", $this->url, "front/secret-number/create", $request);
        // dd($response->json());
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return view('admin.secret-number.create', ['promotions'=> $response->json(),
                'selectedPromotionId' => $id,
            ]);
        }
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $response = $this->forwardRequest(
            'POST',
            $this->url,
            'front/secret-number/store',
            $request,
        );
        if ($response->status() === 422) {
            return redirect()->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->back()
                ->with('success', 'Promoaksiya muvaffaqiyatli saqlandi.');
        }

        return redirect()->back()->with('error', 'Promoaksiya saqlanmadi.');
    }
    public function in_promotion_data(Request $request, $id)
    {
        $locale = app()->getLocale();
        $request->merge(['locale' => $locale]);
        Log::info("ishladi");

        $response = $this->forwardRequest("GET", $this->url, 'front/secret-number/' . $id . '/secretdata', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }

    public function delete(Request $request, $id)
    {
        Log::info("ishladi bu");
        $response = $this->forwardRequest("POST", $this->url, "front/secret-number/{$id}/delete", $request);
        Log::info("data", ['data' => $response->json()]);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json([
                'success' => true,
                'message' => 'Sirli raqam muvaffaqiyatli o‘chirildi.'
            ]);        }
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
        $response = $this->forwardRequest("GET", $this->url, "front/secret-number/{$id}/edit", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
        $data = $response->json();

            // dd($data);
        return view('admin.secret-number.edit', $response->json());
        }
        return back()->with('error', 'Promoaksiya topilmadi.');
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        $response = $this->forwardRequest(
            'POST',
            $this->url,
            "front/secret-number/{$id}/update", // yangilanayotgan promo
            $request,
        );
        // dd($response->json());

        if ($response->status() === 422) {
            return redirect()->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->back()
                ->with('success', 'Promoaksiya muvaffaqiyatli yangilandi.');
        }

        return redirect()->back()->with('error', 'Promoaksiya yangilanmadi.');
    }

    public function show(Request $request, $id){
        $response = $this->forwardRequest(
            'GET',
            $this->url,
            "front/secret-number/{$id}/show", // yangilanayotgan promo
            $request,
        );
        if ($response instanceof \Illuminate\Http\Client\Response) {
            $data = $response->json();

            // dd($data);
            return view('admin.secret-number.show', $response->json());
        }
        return back()->with('error', 'Promoaksiya topilmadi.');
    }

}
