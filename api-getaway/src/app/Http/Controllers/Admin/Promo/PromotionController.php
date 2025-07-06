<?php
namespace App\Http\Controllers\Admin\Promo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromotionController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.auth_service');
    }
    public function index()
    {
        return view('admin.promotion.index');
    }
    public function update(Request $request, $id)
    {
        $response = $this->forwardRequest("PUT", $this->url, "front/promo/{$id}/update", $request);

        if ($response instanceof \Illuminate\Http\Client\Response  && $response->ok()) {
            return redirect()->route('admin.users.index')->with('success', 'Foydalanuvchi yangilandi.');
        }

        if ($response->status() === 422) {
            // Validation error response from auth-service
            $errors    = $response->json('errors') ?? [];
            $errorJson = json_encode($response->json(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            Log::error('Auth service validation error', ['response' => $errorJson]);
            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }

        // Log other errors from auth service
        $errorJson = json_encode($response->json(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Log::error('Auth service update error', ['response' => $errorJson]);

        return redirect()->back()->with('error', 'Foydalanuvchini yangilashda xatolik.');
    }

    // public function getDistricts(Request $request, $regionId)
    // {

    //     try {
    //         $response = $this->forwardRequest("GET", $this->url, "/regions/{$regionId}/districts", $request);
    //         return response()->json($response->json(), $response->status());

    //     } catch (\Throwable $e) {
    //         Log::error("getDistricts exception", [
    //             'region_id' => $regionId,
    //             'error'     => $e->getMessage(),
    //             'trace'     => $e->getTraceAsString(),
    //         ]);
    //     }

    //     return response()->json(['message' => 'Auth service error'], 500);
    // }
    // public function data(Request $request)
    // {
    //     $response = $this->forwardRequest("GET", $this->url, 'front/users/data', $request);
    //     if ($response instanceof \Illuminate\Http\Client\Response) {
    //         return response()->json($response->json(), $response->status());
    //     }
    //     return response()->json(['message' => 'Auth service error'], 500);
    // }

    // public function edit(Request $request, $id)
    // {
    //     $response = $this->forwardRequest("POST", $this->url, "front/users/{$id}/edit", $request);
    //     if ($response->ok()) {
    //         $data = $response->json();
    //         return view('admin.users.edit', compact('data'));
    //     }
    //     return redirect()->back()->with('error', 'Foydalanuvchi topilmadi.');
    // }

    // public function delete(Request $request, $id)
    // {
    //     $response = $this->forwardRequest("POST", $this->url, "front/users/{$id}/delete", $request);
    //     if ($response->ok()) {
    //         return redirect()->back()->with('success', 'Foydalanuvchi o‘chirildi.');
    //     }
    //     return redirect()->back()->with('error', 'Foydalanuvchini o‘chirishda xatolik.');
    // }

    // public function changeStatus(Request $request, $id)
    // {
    //     $response = $this->forwardRequest("POST", $this->url, "front/users/{$id}/status", $request);

    //     if ($response instanceof \Illuminate\Http\Client\Response) {
    //         return response()->json($response->json(), $response->status());
    //     }
    //     return response()->json(['message' => 'Auth service error'], 500);
    // }
}
