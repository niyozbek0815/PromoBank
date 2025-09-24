<?php

namespace App\Http\Controllers\WebApp;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPromocodeRequest;
use App\Repositories\PromotionRepository;
use App\Services\ViaPromocodeService;
use Illuminate\Support\Facades\Log;

class PromotionsController extends Controller
{
    public function __construct(
        private ViaPromocodeService $viaPromocodeService,
        private PromotionRepository $promotionRepository,
    ) {
        $this->viaPromocodeService = $viaPromocodeService;
        $this->promotionRepository = $promotionRepository;
    }
    public function viaPromocode(SendPromocodeRequest $request, $id)
    {
        $user = $request->all();
        Log::info(message: "User ID:",context: ['user'=>$user]);

        $ids = $user['id'];
        $req = $request->validated();
        $data = $this->viaPromocodeService->proccess($req, $user, $id);

        if (!empty($data['promotion'])) {
            return $this->errorResponse('Promotion not found.', ['token' => ['Promotion not found.']], 404);
        }
        if (!empty($data['promocode'])) {
            return $this->errorResponse('Promocode not found.', ['token' => ['Promocode not found.']], 404);
        }
        return $data['action'] === "claim"
            ? $this->errorResponse($data['message'] ?? "Kechirasiz promocodedan avval foydalanilgan", ['token' => [$data['message'] ?? "Kechirasiz promocodedan avval foydalanilgan"]], 422)
            : $this->successResponse($data, $data['message'] ?? "Promocode movaffaqiyatli ro'yhatga olindi");
    }
}
