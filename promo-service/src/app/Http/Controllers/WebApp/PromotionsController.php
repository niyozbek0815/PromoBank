<?php

namespace App\Http\Controllers\WebApp;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPromocodeRequest;
use App\Repositories\PromotionRepository;
use App\Services\ReceiptScraperService;
use App\Services\ReceiptWebAppService;
use App\Services\ViaPromocodeService;
use Illuminate\Support\Facades\Log;

class PromotionsController extends Controller
{
    public function __construct(
        private ViaPromocodeService $viaPromocodeService,

        private PromotionRepository $promotionRepository,
        private ReceiptWebAppService $receiptWebAppService,
        private ReceiptScraperService $scraper,
    ) {

    }
    public function viaPromocode(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        $req = $request->validated();
        $data = $this->viaPromocodeService->proccess($req, $user, $id, 'telegram');
         if (!empty($result['promotion'])) {
            return response()->json([
                'success' => false,
                'status' => 'failed',
                'message' => ['Promotion not found.'],
                'errors' => ['token' => ['Promotion not found.']],
            ], 404);
        }
        $status = $data['status'] ?? null;
        $message = $data['message'] ?? null;

        if (in_array($status, ['claim', 'invalid'], true)) {
            return response()->json([
                'success' => false,
                'status' => 'failed',
                'message' => $message ,
                'errors' => ['promocode' => [$message]],
            ], 422);
        }


        return $this->successResponse(
            $data,
            $message
        );
    }
    public function viaReceipt(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        Log::info("user", ['user' => $user]);
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
