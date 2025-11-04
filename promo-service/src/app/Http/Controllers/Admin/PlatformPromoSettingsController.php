<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformPromoSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlatformPromoSettingsController extends Controller
{
    public function edit()
    {
        $settings = PlatformPromoSetting::firstOrFail();
        Log::info('Platform promo sozlamalari olingan', [
            'settings' => $settings,
        ]);
        return response()->json(['settings' => $settings->toArray()]);
    }

    public function update(Request $request, $id)
    {
        $settings = PlatformPromoSetting::findOrFail($id);

        $validated = $request->validate([
            'scanner_points' => 'required|integer|min:0',
            'refferal_start_points' => 'required|integer|min:0',
            'refferal_registered_points' => 'required|integer|min:0',
            'win_message' => 'required|array',
            'win_message.uz' => 'required|string|max:255',
            'win_message.ru' => 'required|string|max:255',
            'win_message.en' => 'required|string|max:255',
            'win_message.kr' => 'required|string|max:255',
        ]);
        $settings->update($validated);
        return redirect()
            ->back()
            ->with('success', 'Platforma promo sozlamalari muvaffaqiyatli yangilandi.');
    }
}
