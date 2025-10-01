<?php
namespace App\Http\Controllers\Admin\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected $url, $auth_url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
        $this->auth_url = config(key: 'services.urls.auth_service');
    }
    public function index()
    {
        return view('admin.company.index');
    }
    public function create(Request $request, )
    {
        $authResponse = $this->forwardRequest("GET", $this->auth_url, "front/users/clients", $request);
        $clients = $authResponse->json();
        return view('admin.company.create', compact('clients'));
    }
    public function store(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "front/company/store", $request, 'logo');
        if ($response instanceof \Illuminate\Http\Client\Response && $response->ok()) {
            return redirect()->route('admin.company.index')->with('success', 'Foydalanuvchi yaratildi.');
        }
        if ($response instanceof \Illuminate\Http\Client\Response && $response->status() === 422) {
            $errors = $response->json('errors') ?? [];
            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }
        return redirect()->back()->with('error', 'Foydalanuvchini yaratishda xatolik.');
    }

    public function data(Request $request)
    {
        $locale = app()->getLocale();
        $request->merge(['locale' => $locale]);
        $response = $this->forwardRequest("GET", $this->url, 'front/company/data', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Auth service error'], 500);
    }
    public function edit(Request $request, $id)
    {
        try {
            // 1️⃣ So‘rovlar
            $promoResponse = $this->forwardRequest("POST", $this->url, "front/company/{$id}/edit", $request);
            $clientsResponse = $this->forwardRequest("GET", $this->auth_url, "front/users/clients", $request);

            // 2️⃣ Response obyektlarini tekshirish
            if (
                !$promoResponse instanceof \Illuminate\Http\Client\Response ||
                !$clientsResponse instanceof \Illuminate\Http\Client\Response
            ) {
                return view('frontend.error', [
                    'status' => 500,
                    'message' => 'Xizmat bilan aloqa o‘rnatilmadi.'
                ]);
            }

            // 3️⃣ Asosiy kompaniya so‘rovini tekshirish
            if ($promoResponse->failed()) {
                return view('frontend.error', [
                    'status' => $promoResponse->status(),
                    'message' => $promoResponse->json('message') ?? 'Kompaniya maʼlumotlarini olishda xatolik.'
                ]);
            }

            // 4️⃣ Klientlar so‘rovini tekshirish
            if ($clientsResponse->failed()) {
                return view('frontend.error', [
                    'status' => $clientsResponse->status(),
                    'message' => $clientsResponse->json('message') ?? 'Klientlar ro‘yxatini olishda xatolik.'
                ]);
            }

            // 5️⃣ Maʼlumotlarni olish
            $promoData = $promoResponse->json() ?? [];
            $clientsData = $clientsResponse->json() ?? [];

            // 6️⃣ Birlashtirish
            $mergedData = array_merge($promoData, [
                'clients' => $clientsData['data'] ?? $clientsData
            ]);
            return view('admin.company.edit', data: ['data' => $mergedData]);

        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            abort(404, 'Bunday kompaniya topilmadi.');
        } catch (\Throwable $e) {
            report($e); // Logga yozib qo‘yamiz
            abort(500, 'Serverda ichki xatolik yuz berdi.');
        }
    }
    public function update(Request $request, $id)
    {
        // if ($request->hasFile('logo')) {
        //     dd($request->file('logo'));
        // }
        $response = $this->forwardRequest("PUT", $this->url, "front/company/{$id}/update", $request, 'logo');
        if ($response instanceof \Illuminate\Http\Client\Response && $response->ok()) {
            return redirect()->route('admin.company.index')->with('success', 'Foydalanuvchi yangilandi.');
        }

        if ($response->status() === 422) {
            // Validation error response from auth-service
            $errors = $response->json('errors') ?? [];
            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }
        return redirect()->back()->with('error', 'Foydalanuvchini yangilashda xatolik.');
    }

    public function delete(Request $request, $id)
    {
        $response = $this->forwardRequest("POST", $this->url, "front/company/{$id}/delete", $request);
        if ($response->ok()) {
            return redirect()->back()->with('success', 'Foydalanuvchi o‘chirildi.');
        }
        return redirect()->back()->with('error', 'Foydalanuvchini o‘chirishda xatolik.');
    }

    public function changeStatus(Request $request, $id)
    {

        $response = $this->forwardRequest("POST", $this->url, "front/company/{$id}/status", $request);

        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Auth service error'], 500);
    }
    public function forpromotion(Request $request)
    {
        $authResponse = $this->forwardRequest("GET", $this->url, "front/company/forpromotion", $request);
        $clients = $authResponse->json();
        return view('admin.company.create', compact('clients'));
    }

}
