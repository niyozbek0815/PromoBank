<?php

namespace App\Http\Controllers\Mobil;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ViaPromocodeService;
use App\Http\Resources\PromotionResource;
use App\Repositories\PromotionRepository;
use App\Http\Requests\SendPromocodeRequest;
use App\Http\Requests\SendReceiptRequest;
use App\Http\Resources\PromoHistoryRecource;
use App\Models\PromoCodeUser;
use App\Services\ViaReceiptService;
use Illuminate\Support\Facades\Cache;

class PromoController extends Controller
{
    public function __construct(
        private ViaPromocodeService $viaPromocodeService,
        private PromotionRepository $promotionRepository,
        private ViaReceiptService $viaPromeService,
    ) {
        $this->viaPromocodeService = $viaPromocodeService;
        $this->promotionRepository = $promotionRepository;
        $this->viaPromeService = $viaPromeService;
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

    public function viaReceipt(SendReceiptRequest $request, $id)
    {
        $user = $request['auth_user'];
        $req = $request->validated();
        // return PromotionProduct::where(('shop_id'), 11)
        //     ->get();
        // return  PromotionShop::with('products')
        //     ->where('name', $req['name'])
        //     ->where('promotion_id', $id)
        //     ->first();
        // return Promotions::whereHas('platforms', function ($query) {
        //     $query->where('name', 'mobile');
        // })
        //     ->whereHas('participationTypes.participationType', function ($query) use ($req) {
        //         $query->whereIn('slug', ['receipt_scan']);
        //     })
        //     ->with([
        //         'participationTypes.participationType'
        //     ])
        //     ->select('id', 'company_id', 'name', 'title', 'description', 'start_date', 'end_date')
        //     ->get();
        $data = $this->viaPromeService->process($req, $user, $id);
        if (!empty($data['promotion'])) {
            return $this->errorResponse('Promotion not found.', 'Promotion not found.', 404);
        }
        return $data['action'] === "claim"
            ? $this->errorResponse($data['message'] ?? "Kechirasiz promocodedan avval foydalanilgan", 422)
            : $this->successResponse($data, $data['message'] ?? "Yutuq mavjud emas iltimos yana qayta urunib ko'ring");
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
