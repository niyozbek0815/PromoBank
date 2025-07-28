<?php
namespace App\Http\Controllers\Admin\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    protected $url, $auth_url;
    public function __construct()
    {
        $this->url      = config('services.urls.promo_service');
        $this->auth_url = config(key: 'services.urls.auth_service');
    }
    public function index()
    {
        return view('admin.company.index');
    }
    public function create(Request $request, )
    {
        $authResponse = $this->forwardRequest("GET", $this->auth_url, "front/users/clients", $request);
        $clients      = $authResponse->json();

        return view('admin.company.create', compact('clients', ));
    }
    public function store(Request $request)
    {

        $response = $this->forwardRequest("POST", $this->url, "front/company/store", $request, 'logo');
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->ok()) {
            return redirect()->route('admin.users.index')->with('success', 'Foydalanuvchi yaratildi.');
        }
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->status() === 422) {
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
        $promoResponse = $this->forwardRequest("POST", $this->url, "front/company/{$id}/edit", $request);
        $authResponse  = $this->forwardRequest("GET", $this->auth_url, "front/users/clients", $request);
        $clients       = $authResponse->json();
        if ($promoResponse instanceof \Illuminate\Http\Client\Response) {
            if ($promoResponse->ok()) {
                $data = $promoResponse->json();
                Log::info('data', ['data' => $data]);
                return view('admin.company.edit', compact('data', 'clients'));
            }
        }
        return redirect()->back()->with('error', 'Foydalanuvchi topilmadi.');
    }
    public function update(Request $request, $id)
    {
        // if ($request->hasFile('logo')) {
        //     dd($request->file('logo'));
        // }
        $response = $this->forwardRequest("PUT", $this->url, "front/company/{$id}/update", $request, 'logo');
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->ok()) {
            return redirect()->route('admin.company.index')->with('success', 'Foydalanuvchi yangilandi.');
        }

        if ($response->status() === 422) {
            // Validation error response from auth-service
            $errors = $response->json('errors') ?? [];return redirect()->back()
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
        $clients      = $authResponse->json();
        return view('admin.company.create', compact('clients'));
    }

}
