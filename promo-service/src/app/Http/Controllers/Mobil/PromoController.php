<?php
namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPromocodeRequest;
use App\Http\Resources\PromoHistoryRecource;
use App\Http\Resources\PromotionResource;
use App\Models\PromoCodeUser;
use App\Repositories\PromotionRepository;
use App\Services\ViaPromocodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PromoController extends Controller
{
    public function __construct(
        private ViaPromocodeService $viaPromocodeService,
        private PromotionRepository $promotionRepository,
    ) {
        $this->viaPromocodeService = $viaPromocodeService;
        $this->promotionRepository = $promotionRepository;
    }

    public function index()
    {
        $cacheKey   = 'promotions:platform:mobile:page:' . request('page', 1);
        $ttl        = now()->addMinutes(5); // 5 daqiqa kesh
        $promotions = Cache::store('redis')->remember($cacheKey, $ttl, function () {
            return $this->promotionRepository->getAllPromotionsForMobile();
        });
        return $this->successResponse(['promotions' => PromotionResource::collection($promotions)], "success");
    }
    public function viaPromocode(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        $req  = $request->validated();
        $data = $this->viaPromocodeService->proccess($req, $user, $id);
        // return $data;
        if (! empty($data['promotion'])) {
            return $this->errorResponse('Promotion not found.', ['promotion' => 'Promotion not found.'], 404);
        }
        if (! empty($data['promocode'])) {
            return $this->errorResponse('Promocode not found.', ['promocode' => 'Promocode not found.'], 404);
        }
        return $data['action'] === "claim"
        ? $this->errorResponse($data['message'] ?? "Kechirasiz promocodedan avval foydalanilgan", ['promocode' => $data['message'] ?? "Kechirasiz promocodedan avval foydalanilgan"], 422)
        : $this->successResponse($data, $data['message'] ?? "Promocode movaffaqiyatli ro'yhatga olindi");
    }

    public function listParticipationHistory(Request $request, $promotionId)
    {
        $user       = $request['auth_user'];
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