<?php

namespace App\Http\Controllers\WebApp;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPromocodeRequest;
use App\Repositories\PromotionRepository;
use App\Services\ReceiptScraperService;
use App\Services\ReceiptWebAppService;
use App\Services\WebAppPromocodeService;
use Illuminate\Support\Facades\Log;

class PromotionsController extends Controller
{
    public function __construct(
        private WebAppPromocodeService $webAppPromocodeService,
        private PromotionRepository $promotionRepository,
        private ReceiptWebAppService $receiptWebAppService,
        private ReceiptScraperService $scraper,
    ) {

    }


    public function viaPromocode(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        Log::info(message: "User ID:", context: ['user' => $user]);
        $req = $request->validated();
        $result = $this->webAppPromocodeService->proccess($req, $user, $id);

        // ❌ Promocode topilmadi
        if (!empty($result['promocode'])) {
            return response()->json([
                'success' => false,
                'status' => 'failed',
                'message' => ['Promocode not found.'],
                'errors' => ['token' => ['Promocode not found.']],
            ], 404);
        }
        if (!empty($result['promotion'])) {
            return response()->json([
                'success' => false,
                'status' => 'failed',
                'message' => ['Promotion not found.'],
                'errors' => ['token' => ['Promotion not found.']],
            ], 404);
        }
        if ($result['action'] === "claim") {
            return response()->json([
                'success' => false,
                'status' => 'failed',
                'message' => $result['message'] ?? "Kechirasiz, promocodedan avval foydalanilgan",
                'errors' => ['token' => [$result['message'] ?? "Kechirasiz, promocodedan avval foydalanilgan"]],
            ], $result['code'] ?? 422);
        }
        return response()->json([
            'success' => true,
            'status' => 'success',
            'data' => $result,
            'message' => $result['message'] ?? "Promocode muvaffaqiyatli ro'yhatga olindi",
        ], 200);
    }
    public function viaReceipt(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        $req = $request->validated();
        $data = $this->scraper->scrapeReceipt($req);

        $result = $this->receiptWebAppService->proccess($data, $user);
        return $result['status'] === 'failed'
            ? response()->json([
                'success' => false,
                'status' => 'failed',
                'message' => $result['message'] ?? 'Xatolik, birozdan so‘ng qayta urinib ko‘ring',
                'errors' => $result['errors'] ?? [],
            ], $data['code'] ?? 422)
            : response()->json([
                'success' => true,
                'status' => 'success',
                'data' => $data,
                'message' => $result['message'] ?? 'Xatolik, birozdan so‘ng qayta urinib ko‘ring',
            ]);
    }
}
