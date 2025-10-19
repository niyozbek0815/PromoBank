<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaJob;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->mapWithKeys(function ($setting) {

            // Avval val ni olish va decode qilish
            $val = $setting->val;
            if (is_string($val)) {
                $decoded = json_decode($val, true);
                $val = $decoded !== null ? $decoded : $val; // decode muvaffaqiyatli bo'lsa array, aks holda string
            }
            return [$setting->key_name => $val];
        });

        return response()->json(['settings' => $settings]);
    }
    /**
     * Show edit form for all settings
     */
    public function edit()
    {
        // Barcha settings ni key => val tarzida olish
        $settings = Setting::all()->mapWithKeys(function ($setting) {

            // Avval val ni olish va decode qilish
            $val = $setting->val;
            if (is_string($val)) {
                $decoded = json_decode($val, true);
                $val = $decoded !== null ? $decoded : $val; // decode muvaffaqiyatli bo'lsa array, aks holda string
            }
            return [$setting->key_name => $val];
        });

        return response()->json(['settings' => $settings]);
    }

    /**
     * Update all settings
     */

    public function update(Request $request)
    {
        $data = $request->all();

        // File upload
        foreach (['navbar_logo', 'footer_logo'] as $field) {
            if ($request->hasFile($field)) {
                $logoSetting = Setting::where('key_name', $field)->first();
                // $logoSetting->media()->delete();

                $file = $request->file($field); // ✅ To‘g‘ri: input nomi bo‘yicha olish
                $tempPath = $file->store('tmp', 'public');
                Log::info("📎 $field image yuklanmoqda... " . $tempPath);
                Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'logo', $logoSetting['id']));
            }
        }

        // JSON fields
        foreach (['hero_title', 'footer_description', 'footer_bottom', 'languages'] as $field) {
            if (isset($data[$field])) {
                if ($field === 'languages') {
                    $encoded = json_encode([
                        'available' => $data['languages']['available'] ?? ['uz', 'ru', 'kr','en'],
                        'default'   => $data['languages']['default'] ?? 'uz',
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    $encoded = json_encode($data[$field], JSON_UNESCAPED_UNICODE);
                }

                Setting::updateOrCreate(
                    ['key_name' => $field],
                    ['val' => $encoded]
                );
            }
        }


        return response()->json(['message' => 'Settings updated successfully']);
    }
}
