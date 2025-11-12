<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProgressBarController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $response = $this->forwardRequest(
            'POST',
            $this->url,
            "front/progress-bar/{$id}/update", // yangilanayotgan promo
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
}




