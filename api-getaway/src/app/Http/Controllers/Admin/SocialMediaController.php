<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }

    public function data(Request $request, $id)
    {
        $endpoint = "front/socialcompany/{$id}/data";
        $response = $this->forwardRequest("GET", $this->url, $endpoint, $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Auth service error'], 500);
    }
    public function delete(Request $request, $id)
    {
        $endpoint = "front/socialcompany/{$id}/delete";
        $method   = "POST";
        $response = $this->forwardRequest($method, $this->url, $endpoint, $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->back()->with('success', 'Link o‘chirildi.');
        }
        return redirect()->back()->with('error', 'Foydalanuvchini o‘chirishda xatolik.');
    }
    public function store(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, "front/socialcompany/store", $request, 'logo');
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->ok()) {
            return redirect()->back()->with('success', 'Link yaratildi.');
        }

        if ($response instanceof \Illuminate\Http\Client\Response  && $response->status() === 422) {
            $errors    = $response->json('errors') ?? [];
            $errorJson = json_encode($response->json(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }
        return redirect()->back()->with('error', 'Linkni yaratishda xatolik.');
    }

}
