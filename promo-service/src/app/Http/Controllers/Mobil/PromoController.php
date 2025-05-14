<?php

namespace App\Http\Controllers\Mobil;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ViaPromocodeService;
use App\Http\Resources\PromotionResource;
use App\Repositories\PromotionRepository;
use App\Http\Requests\SendPromocodeRequest;
use App\Http\Requests\SendReceiptRequest;
use App\Jobs\CreateReceiptAndProductJob;
use App\Models\PromotionProduct;
use App\Models\Promotions;
use App\Models\PromotionShop;
use App\Services\ViaReceiptService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

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
        // return $this->successResponse(['promotions' => "S"], "success");
        return  $this->successResponse($data, $data['message'] ?? "Promocode movaffaqiyatli ro'yhatga olindi");
    }

    public function checkStatus(Request $request, $promotionId)
    {
        return $this->successResponse(['promotions' => "success3"], "success");
    }
}