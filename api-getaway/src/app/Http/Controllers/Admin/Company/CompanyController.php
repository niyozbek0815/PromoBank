<?php
namespace App\Http\Controllers\Admin\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }
    public function index()
    {

        return view('admin.company.index');
    }
    public function data(Request $request)
    {
        $locale = app()->getLocale();
        Log::info('datag keldi' . $locale);

        $request->merge(['locale' => $locale]);
        $response = $this->forwardRequest("GET", $this->url, 'front/company/data', $request);

        if ($response instanceof \Illuminate\Http\Client\Response) {
            Log::info('Company data response:', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return response()->json($response->json(), $response->status());
        }

        Log::error('Auth service error');
        return response()->json(['message' => 'Auth service error'], 500);
    }
    public function edit(Request $request, $id)
    {
        $response = $this->forwardRequest("POST", $this->url, "front/users/{$id}/edit", $request);
        if ($response->ok()) {
            $data = $response->json();
            return view('admin.users.edit', compact('data'));
        }
        return redirect()->back()->with('error', 'Foydalanuvchi topilmadi.');
    }

    public function delete(Request $request, $id)
    {
        $response = $this->forwardRequest("POST", $this->url, "front/users/{$id}/delete", $request);
        if ($response->ok()) {
            return redirect()->back()->with('success', 'Foydalanuvchi o‘chirildi.');
        }
        return redirect()->back()->with('error', 'Foydalanuvchini o‘chirishda xatolik.');
    }

    public function changeStatus(Request $request, $id)
    {
        Log::info('Sending changeStatus request', [
            'id'      => $id,
            'payload' => $request->all(),
        ]);

        $response = $this->forwardRequest("POST", $this->url, "front/users/{$id}/status", $request);

        if ($response instanceof \Illuminate\Http\Client\Response) {
            Log::info('Received changeStatus response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return response()->json($response->json(), $response->status());
        }

        Log::error('Auth service error on changeStatus');
        return response()->json(['message' => 'Auth service error'], 500);
    }

}