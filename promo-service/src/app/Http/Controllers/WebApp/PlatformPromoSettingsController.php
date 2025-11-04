<?php

namespace App\Http\Controllers\WebApp;

use App\Http\Controllers\Controller;
use App\Models\EncouragementPoint;
use App\Models\PlatformPromoSetting;
use App\Models\Referrals;
use App\Models\UserPointBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlatformPromoSettingsController extends Controller
{
    public function index()
    {

        $settings = PlatformPromoSetting::firstOrFail();

        return response()->json([
            'refferal_start_points' => $settings->refferal_start_points,
            'refferal_registered_points' => $settings->refferal_registered_points,
        ]);
    }
    public function addPointsToUser(Request $request)
    {
        $validated = $request->validate([
            'promoball' => ['required', 'integer', 'min:1', 'max:1000000'],
            'referrer_id' => ['required', 'integer'],
            'referred_id' => ['nullable', 'integer', 'different:referrer_id'],
            'referrer_chat_id' => ['required', 'string', 'regex:/^\d{5,20}$/'],
            'referred_chat_id' => ['required', 'string', 'regex:/^\d{5,20}$/', 'different:referrer_chat_id'],
            'referred_username' => ['required', 'string', 'max:255']
        ]);

        Log::info('Add points to user request received', [
            'request_data' => $validated,
            'timestamp' => now()->toDateTimeString(),
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // delete all records from these tables (use query()->delete() to perform mass delete)
                // EncouragementPoint::query()->delete();

                // // Referrals tozalash (referrer yoki referred chat orqali)
                // Referrals::query()->delete();
                // UserPointBalance::query()->delete();
                // 1️⃣ User balansini olish yoki yaratish
                $balance = UserPointBalance::firstOrCreate(
                    ['user_id' => $validated['referrer_id']],
                    ['balance' => 0]
                );
                $balance->increment('balance', $validated['promoball']);

                // 2️⃣ Referral yozuvini yaratish yoki yangilash
                $referral = Referrals::recordStart(
                    $validated['referrer_id'],
                    $validated['referrer_chat_id'],
                    $validated['referred_chat_id'],
                    $validated['referred_username'] // 🔹 To‘g‘ri syntax
                );

                // if (!empty($validated['referred_id'])) {
                //     $referral->register($validated['referred_id']);
                // }

                // 3️⃣ Mukofotni berish va EncouragementPoint yaratish
                // $encouragement = $referral->givePointsWithoutActivation($validated['promoball'], 'referral_start');                Log::info(' start datalar', [
                //     'encouragement' => EncouragementPoint::get(),
                //     'referral' => Referrals::get(),
                //     'balance' => $balance
                // ]);
                return compact('balance', 'referral', 'encouragement');
            });

            return response()->json([
                'success' => true,
                'message' => "Foydalanuvchiga {$validated['promoball']} promo ball qo‘shildi va referral yangilandi.",
            ]);

        } catch (\Throwable $e) {
            Log::error('Referral yoki point yozishda xatolik', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function addPointsToUserRegister(Request $request)
    {
        $validated = $request->validate([
            'promoball' => ['required', 'integer', 'min:1', 'max:1000000'],
            'referred_id' => ['required', 'integer'],
            'referred_chat_id' => ['required', 'string', 'regex:/^\d{5,20}$/', 'different:referrer_chat_id'],
            'username' => ['required', 'string', 'max:255']
        ]);

        Log::info('Add points to user request received', [
            'request_data' => $validated,
            'timestamp' => now()->toDateTimeString(),
        ]);

        try {
           $data= DB::transaction(function () use ($validated) {
                $referral = Referrals::where('referred_chat_id', $validated['referred_chat_id'])
                    ->where('status', Referrals::STATUS_STARTED) // faqat start bosilganlar
                    ->first();
                if (!$referral) {
                    Log::info("referall", ['exists' => false, 'data' => $referral]);
                    return false; // Start bosilmagan referral topilmadi
                }
                $result = $referral->registerWithPoints(
                    $validated['referred_id'],
                    $validated['username'],
                    $validated['promoball']
                );
                    // Log::info('register datalar', [
                    // 'encouragement' => EncouragementPoint::get(),
                    // 'referral' => Referrals::get(),
                    // 'balance' => $result['balance']
                    // ]);
                return [
                    'chat_id'=>$referral['referrer_chat_id'],
                    'exists' => true,
                    'message' => "Foydalanuvchiga {$validated['promoball']} promo ball qo‘shildi va referral yangilandi.",
                ];
            });

            return response()->json($data);

        } catch (\Throwable $e) {
            Log::error('Referral yoki point yozishda xatolik', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
