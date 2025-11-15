<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnTvVaucherController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }
    public function index()
    {
        // dd("data");
        return view('admin.ontv.index');
    }
    public function create(Request $request){
        return view('admin.ontv.create');
    }
    public function store(Request $request){
        $response = $this->forwardRequest(
            'POST',
            $this->url,
            'front/ontv/store',
            $request,
        );
        if ($response->status() === 422) {
            return redirect()->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return redirect()->back()
                ->with('success', 'Vaucher muvaffaqiyatli saqlandi.');
        }

        return redirect()->back()->with('error', 'Vaucher saqlanmadi.');
    }
    public function import(Request $request){
        $response = $this->forwardRequestMedias(
            'POST',
            $this->url,
            'front/ontv/import',
            $request,
            ['file']
        );
        // dd($response->json());
        if ($response->status() === 422) {
            return redirect()->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response->ok()) {
            $data = $response->json();
            return redirect()->back()->with('success', $data["message"]);
        }

        return redirect()->back()->with('error', 'Generatsiya qilishda xatolik.');

    }
    public function data(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, 'front/ontv/data', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
}
