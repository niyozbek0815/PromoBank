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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

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

    public function viaReceipt(SendReceiptRequest $request, $promotionId)
    {
        $user = $request['auth_user'];
        $req = $request->validated();
        Queue::connection('rabbitmq')->push(new CreateReceiptAndProductJob($req, $user));
        return $this->successResponse(['promotions' => "S"], "success");
    }

    public function checkStatus(Request $request, $promotionId)
    {
        return $this->successResponse(['promotions' => "success3"], "success");
    }
}
