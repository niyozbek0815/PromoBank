<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrizeCategoryController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }
    public function data(int $promotion, string $type, Request $request)
    {
        $response = $this->forwardRequest(
            'GET',
            $this->url,
            "front/prize-category/{$promotion}/type/{$type}/data",
            $request,
        );
        Log::info($response->json());

        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return response()->json($response->json(), $response->status());
        }
        abort(404, 'Xizmatdan maʼlumot olishda xatolik yuz berdi.');
    }

    public function show(int $promotion, string $type, Request $request)
    {
        $response = $this->forwardRequest(
            'GET',
            $this->url,
            "front/prize-category/{$promotion}/type/{$type}",
            $request,
        );
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            $data = $response->json();

            return view("admin.prize.manual_show", compact('data'));
        }
        abort(404, 'Xizmatdan maʼlumot olishda xatolik yuz berdi.');
    }
}
