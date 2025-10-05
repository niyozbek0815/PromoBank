<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrizeController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.promo_service');
    }
    public function index(Request $request)
    {
        return view('admin.prize.index');
    }
    public function data(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/prize/data", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function changeStatus(Request $request, $id)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/prize/{$id}/status", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Promo service error'], 500);
    }
    public function edit(Request $request, $prize)
    {
        $response = $this->forwardRequest("GET", $this->url, "front/prize/{$prize}/edit", $request);
        // dd( $response->json('prize'));
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            $data = $response->json();
            // dd($data);
            return view('admin.prize.edit', $response->json());
        }

        abort(404, 'Xizmatdan maʼlumot olishda xatolik yuz berdi.');
    }

    public function update(Request $request, $prizeId)
    {
        $response = $this->forwardRequest(
            'POST',
            $this->url,
            "front/prize/{$prizeId}/update",
            $request
        );
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return redirect()
                ->back()
                ->with('success', 'Sovg‘a ma’lumotlari muvaffaqiyatli yangilandi.');
        }

        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }
    public function storeMessage(Request $request, $prizeId)
    {
        // dd($request->all());
        $response = $this->forwardRequest(
            'POST',
            $this->url,
            "front/prize/{$prizeId}/message",
            $request
        );
        // 2️⃣ Validatsiya xatoliklari (422)
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return redirect()
                ->back()
                ->with('success', 'Sovg‘a ma’lumotlari muvaffaqiyatli yangilandi.');
        }



        // 3️⃣ Boshqa xatoliklar
        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }
    public function storeRules(Request $request, $prizeId)
    {
        // dd($request->all());
        $response = $this->forwardRequest(
            'POST',
            $this->url,
            "front/prize/{$prizeId}/smartrules",
            $request
        );
        // dd($response->json());
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return redirect()
                ->back()
                ->with('success', 'Smart qoidalar muvaffaqiyatli yangilandi.');
        }

        // 2️⃣ Validatsiya xatoliklari (422)
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }

        // 3️⃣ Boshqa xatoliklar
        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }
    public function deleteRule(Request $request, $prizeId, $ruleId)
    {
        $response = $this->forwardRequest(
            'DELETE',
            $this->url,
            "front/prize/{$prizeId}/smartrules/{$ruleId}",
            $request
        );
        // Validatsiya yoki mantiqiy xatolik (422)
        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return redirect()
                ->back()
                ->with('success', 'Smart qoida muvaffaqiyatli o‘chirildi.');
        }



        // Boshqa xatoliklar
        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }
    public function autobind(Request $request, $prizeId)
    {

        $response = $this->forwardRequest(
            "POST",
            $this->url,
            "front/prize/{$prizeId}/autobind",
            $request
        );
        if ($response instanceof \Illuminate\Http\Client\Response) {
            $settings = $response->json('settings');
            if ($settings === null) {
                return redirect()
                    ->back()
                    ->with(['success' => "Sovg'ani avtomatik bog'lash muvaffaqiyatli amalga oshirildi."]);
            }
        }
        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }
    public function autobindDelete(Request $request, $prizeId, $promocodeId)
    {
        $response = $this->forwardRequest(
            "POST",
            $this->url,
            "front/prize/{$prizeId}/autobind/{$promocodeId}",
            $request
        );
        if ($response instanceof \Illuminate\Http\Client\Response) {
            $settings = $response->json('settings');
            if ($settings === null) {
                return redirect()
                    ->back()
                    ->with(['success' => "Sovg'ani avtomatik bog'lash muvaffaqiyatli amalga oshirildi."]);
            }
        }
    }
}
