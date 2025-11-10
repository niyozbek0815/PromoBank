<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionProgressBar;
use Illuminate\Http\Request;

class ProgressBarController extends Controller
{
    public function update(Request $request, int $promotionId)
    {
        // Validatsiya
        $validated = $request->validate([
            'daily_points' => 'required|integer|min:0',
            'step_0_threshold' => 'required|integer|min:0',
            'step_1_threshold' => 'required|integer|min:0',
            'step_2_threshold' => 'required|integer|min:0',
            'day_start_at' => 'required|string|regex:/^\d{2}:\d{2}$/', // HH:MM format
        ]);

        // Update yoki create qilish
        $progressBar = PromotionProgressBar::updateOrCreate(
            ['promotion_id' => $promotionId], // qidirish sharti
            [
                'daily_points' => $validated['daily_points'],
                'step_0_threshold' => $validated['step_0_threshold'],
                'step_1_threshold' => $validated['step_1_threshold'],
                'step_2_threshold' => $validated['step_2_threshold'],
                'day_start_at' => $validated['day_start_at'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Progress bar muvaffaqiyatli yangilandi.',
            'progress_bar' => $progressBar
        ]);
    }
}
