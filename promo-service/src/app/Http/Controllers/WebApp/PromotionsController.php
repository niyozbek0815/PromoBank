<?php

namespace App\Http\Controllers\WebApp;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPromocodeRequest;
use App\Models\SalesReceipt;
use App\Repositories\PromotionRepository;
use App\Services\ReceiptScraperService;
use App\Services\ReceiptService;
use App\Services\ViaPromocodeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromotionsController extends Controller
{
    public function __construct(
        private ViaPromocodeService $viaPromocodeService,
        private PromotionRepository $promotionRepository,
        private ReceiptService $receiptService,
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
                'message' => $message,
                'errors' => ['promocode' => [$message]],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => $message,
            'errors' => null,
        ]);
    }
    public function viaReceipt(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        Log::info("user", ['user' => $user]);
        $req = $request->validated();
        $data = $this->scraper->scrapeReceipt($req);

        DB::table('sales_receipts')->truncate();

        $existing = SalesReceipt::where('chek_id', $data['chek_id'])->first();

        if ($existing) {
            Log::warning("❗ Receipt already exists", ['chek_id' => $req['chek_id']]);
            $lang = $req['lang'] ?? 'uz';
            $messages = [
                'uz' => [
                    'message' => 'Ushbu check ID avval ro‘yxatdan o‘tgan.',
                    'field' => 'Ushbu check ID allaqachon mavjud.',
                ],
                'ru' => [
                    'message' => 'Этот чек уже был зарегистрирован ранее.',
                    'field' => 'Этот чек уже существует.',
                ],
                'en' => [
                    'message' => 'This check ID has already been registered.',
                    'field' => 'This check ID already exists.',
                ],
                'kr' => [
                    'message' => '이 영수증 ID는 이미 등록되었습니다.',
                    'field' => '이 영수증 ID는 이미 존재합니다.',
                ],
            ];
            $msg = $messages[$lang] ?? $messages['uz'];
            return response()->json([
                'success' => false,
                'status' => 'fail',
                'message' => $msg['message'],
                'errors' => [
                    'chek_id' => [$msg['field']],
                ],
            ], 422);
        }
        Log::info("ScrapperData", [$data]);
        $result = $this->receiptService->proccess($data, $user, 'telegram');
        Log::info("returnData", [$result]);
        return $result['status'] === 'fail'
            ? response()->json([
                'success' => false,
                'status' => 'fail',
                'message' => $result['messages'] ?? 'Xatolik, birozdan so‘ng qayta urinib ko‘ring',
                'errors' => ['promocode' => [$result['messages']]],
            ], $data['code'] ?? 422)
            : response()->json([
                'success' => true,
                'status' => $result['status'],
                'data' => $data,
                'message' => $result['messages'],
                'errors' => null
            ]);
    }
}
