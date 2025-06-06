<?php

namespace App\Http\Controllers\Mobil;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ViaPromocodeService;
use App\Http\Resources\PromotionResource;
use App\Repositories\PromotionRepository;
use App\Http\Requests\SendPromocodeRequest;
use App\Http\Resources\PromoHistoryRecource;
use App\Models\PromoCodeUser;
use Illuminate\Support\Facades\Log;
use App\Services\SendToQueue;
use App\Services\ViaPromocodeFromSms;
use Illuminate\Support\Facades\Cache;

class PromoController extends Controller
{
    public function __construct(
        private ViaPromocodeService $viaPromocodeService,
        private PromotionRepository $promotionRepository,
        private ViaPromocodeFromSms $viaPromocodeFromSms
    ) {
        $this->viaPromocodeService = $viaPromocodeService;
        $this->promotionRepository = $promotionRepository;
        $this->viaPromocodeFromSms = $viaPromocodeFromSms;
    }
    public function sms(Request $request)
    {
        // return Promotions::with('participationTypesSms', 'promoCodes')
        //     ->whereHas('participationTypesSms')->get();
        $data = [
            'phone' => "+998900191098",
            'promo_code' => "6L3V8PGU1D",
            'short_phone' => "1112",
        ];


        $phone = $data['phone'] ?? null;
        if (!$phone) {
            logger()->warning('Telefon raqam topilmadi', $request->all());
            return response()->json([
                'status' => 'error',
                'message' => 'Telefon raqam topilmadi',
            ]);
        }


        try {

            $response = $this->viaPromocodeFromSms->viaPromocode($data);

            new SendToQueue()->send([
                'phone' => $phone,
                'message' => $response['message']
            ], 'sms_message_sender');

            return response()->json([
                'status' => 'success',
                'message' => 'Promo code muvaffaqiyatli qabul qilindi',
                'data' => $response,
            ]);
        } catch (\Throwable $e) {
            // logger()->error('ProcessSmsPromoJob exception', [
            //     'message' => $e->getMessage(),
            //     'data' => $request->all(), // faqat array yuboriladi
            // ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Xatolik yuz berdi: ' . $e->getMessage(),
            ]);
        }
    }
    public function index()
    {
        $cacheKey = 'promotions:platform:mobile:page:' . request('page', 1);
        $ttl = now()->addMinutes(5); // 5 daqiqa kesh
        $promotions = Cache::store('redis')->remember($cacheKey, $ttl, function () {
            return  $this->promotionRepository->getAllPromotionsForMobile();
        });
        return $this->successResponse(['promotions' => PromotionResource::collection($promotions)], "success");
    }
    public function viaPromocode(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        $req = $request->validated();
        $data = $this->viaPromocodeService->proccess($req, $user, $id);
        // return $data;
        if (!empty($data['promotion'])) {
            return $this->errorResponse('Promotion not found.', 'Promotion not found.', 404);
        }
        if (!empty($data['promocode'])) {
            return $this->errorResponse('Promocode not found.', 'Promocode not found.', 404);
        }
        return $data['action'] === "claim"
            ? $this->errorResponse($data['message'] ?? "Kechirasiz promocodedan avval foydalanilgan", 422)
            : $this->successResponse($data, $data['message'] ?? "Promocode movaffaqiyatli ro'yhatga olindi");
    }



    public function listParticipationHistory(Request $request, $promotionId)
    {
        $user = $request['auth_user'];
        $promo_user = PromoCodeUser::where('promotion_id', $promotionId)
            ->where('user_id', $user['id'])
            ->with([
                'promoCode:promocode,id',
                'receipt:id,chek_id,name,created_at',
                'platform:id,name',
                'promotionProduct:id,name',
                'prize:id,name',
            ])
            ->orderByDesc('created_at')
            ->get(['id', 'promo_code_id', 'platform_id', 'promotion_product_id', 'prize_id', 'receipt_id']);
        return $this->successResponse(['promotions' => PromoHistoryRecource::collection($promo_user)], "success");
    }
}