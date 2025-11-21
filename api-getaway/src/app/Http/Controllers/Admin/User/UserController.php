<?php
namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $url, $notif;
    public function __construct()
    {
        $this->url = config(key: 'services.urls.auth_service');
        $this->notif = config(key: 'services.urls.notification_service');
    }
    public function index()
    {
        return view('admin.users.index');
    }
    public function update(Request $request, $id)
    {
        $response = $this->forwardRequest("PUT", $this->url, "front/users/{$id}/update", $request);
        if ($response instanceof \Illuminate\Http\Client\Response && $response->ok()) {
            return redirect()->route('admin.users.index')->with('success', 'Foydalanuvchi yangilandi.');
        }
        if ($response->status() === 422) {
            $errors = $response->json('errors') ?? [];
            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }
        return redirect()->back()->with('error', 'Foydalanuvchini yangilashda xatolik.');
    }
    public function getDistricts(Request $request, $regionId)
    {
        $response = $this->forwardRequest("GET", $this->url, "/regions/{$regionId}/districts", $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Auth service error'], 500);
    }
    public function data(Request $request)
    {
        $response = $this->forwardRequest("GET", $this->url, 'front/users/data', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Auth service error'], 500);
    }

    public function edit(Request $request, $id)
    {

        try {
            $mainResponse = $this->forwardRequest("POST", $this->url, "front/users/{$id}/edit", $request);
            $devicesResponse = $this->forwardRequest("POST", $this->notif, "front/devices/{$id}", $request);
            if (!$mainResponse instanceof \Illuminate\Http\Client\Response || !$devicesResponse instanceof \Illuminate\Http\Client\Response) {
                return view('frontend.error', [
                    'status' => 500,
                    'message' => 'Xizmat bilan aloqa o‘rnatilmadi.'
                ]);
            }
            if ($mainResponse->failed()) {
                return view('frontend.error', [
                    'status' => $mainResponse->status(),
                    'message' => $mainResponse->json('message') ?? 'Asosiy sahifa maʼlumotlarini olishda xatolik.'
                ]);
            }
            if ($devicesResponse->failed()) {
                return view('frontend.error', [
                    'status' => $devicesResponse->status(),
                    'message' => $devicesResponse->json('message') ?? 'Aksiya maʼlumotlarini olishda xatolik.'
                ]);
            }
            $mainData = $mainResponse->json() ?? [];
            $devicesData = $devicesResponse->json() ?? [];
            if ($devicesResponse->status() == 404) {
                abort(404);
            }
            $mergedData = array_merge($mainData, [
                'devices' => $devicesData['data'] ?? $devicesData
            ]);
            // dd($mergedData);
            return view('admin.users.edit', $mergedData);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return abort(404, 'Bunday aksiya topilmadi.');
        } catch (\Throwable $e) {
            abort(500); // Laravel default 500 sahifasi
        }
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
        $response = $this->forwardRequest("POST", $this->url, "front/users/{$id}/status", $request);

        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return response()->json(['message' => 'Auth service error'], 500);
    }
}