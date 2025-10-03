<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }
    public function index(Request $request)
    {
        return view('admin.messages.index');
    }
    public function data(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/settings/messages/data", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function edit(Request $request,$id)
    {        $response = $this->forwardRequest("GET", $this->url, "front/settings/messages/{$id}/edit", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return view('admin.messages.edit',['message'=>$response->json()]);
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function update(Request $request, $id)
    {
        $response = $this->forwardRequest(
            'POST',
            $this->url,
            "front/settings/messages/{$id}",
            $request
        );
        // dd($response->json());
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
          return view('admin.messages.index')
                ->with('success', 'Sovg‘a ma’lumotlari muvaffaqiyatli yangilandi.');
        }

        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }
}
