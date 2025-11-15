<?php

namespace App\Http\Controllers\WebApp;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPromocodeRequest;
use App\Http\Resources\PromotionShowWebAppResource;
use App\Http\Resources\PromoWebResource;
use App\Models\EncouragementPoint;
use App\Models\PromotionProgressBar;
use App\Models\SalesReceipt;
use App\Repositories\PromotionRepository;
use App\Services\ReceiptScraperService;
use App\Services\ReceiptService;
use App\Services\SecretNumberService;
use App\Services\ViaPromocodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PromotionsController extends Controller
{
    public function __construct(
        private ViaPromocodeService $viaPromocodeService,
        private ReceiptService $receiptService,
        private ReceiptScraperService $scraper,
        private SecretNumberService $secretNumberService,
        private PromotionRepository $promotionRepository,
    ) {

    }
    public function index(Request $request)
    {
        $lang = $request->get('lang', 'uz');
        $page = $request->get('page', 1);
        $cacheKey = "promotions:platform:webapp:lang:{$lang}:page:{$page}";
        $ttl = now()->addMinutes(10);
        // Cache::store('redis')->forget($cacheKey);
        $promotions = Cache::store('redis')->remember($cacheKey, $ttl, function () {
            return $this->promotionRepository->getAllPromotionsForWebAppHome();
        });
        return response()->json(
            PromoWebResource::collection($promotions)->additional(['lang' => $lang])
        );
    }
    public function show(Request $request, $id)
    {
        $lang = $request->get('lang', 'uz'); // Default til 'uz'
        $ttl = now()->addMinutes(3);
                $cacheKey = "promotions:platform:webapp:show:{$id}:lang:{$lang}";
        // Cache::store('redis')->forget($cacheKey);
        $promotion = Cache::store('redis')->remember($cacheKey, $ttl, function () use ($id) {
            return $this->promotionRepository->getAllPromotionsShowForWebHome($id);
        });
        // $user = $request['auth_user'];

        return response()->json(
            (new PromotionShowWebAppResource($promotion))->additional(['lang' => $lang])
        );
    }
    public function rating(Request $request, $id)
    {
        $user = $request['auth_user'];
        Cache::forget("promotion:progressbar:daystart:$id");
        $dayStartAt = Cache::remember("promotion:progressbar:daystart:$id", now()->addMinutes(15), function () use ($id) {
            return PromotionProgressBar::where('promotion_id', $id)->value('day_start_at');
        }) ?? '00:00';

        [$hour, $minute] = explode(':', $dayStartAt);
        $now = now();
        $start = $now->copy()->setTime($hour, $minute);
        if ($now->lt($start)) {
            $start->subDay();
        }
        $end = $start->copy()->addDay();
        // $start = $now->copy()->subWeek(); // bir hafta oldin
        // $start->setTime($hour, $minute);   // start vaqtini dayStartAt ga sozlash

        // $end = $now->copy();
        // $start = $start->copy()->subHours(5);
        // $end = $end->copy()->subHours(5);
        // Foydalanuvchi ballarini faqat shu oraliqda hisoblaymiz
        $usersPoints = EncouragementPoint::getUserTotalAndRank(
            $user['id'],
            ['referral_start', 'referral_register', 'secret_number'],
            "Noma'lum user",
            $start,
            $end
        );

        $topUsers = EncouragementPoint::getTopUsersWithRank(
            ['referral_start', 'referral_register', 'secret_number'],
            $start,
            $end,
            ($usersPoints && $usersPoints['rank'] > 100) ? 99 : 100
        );
        Log::info("ahowAjaxDAta", [
            'user' => $user,
            'user_poinst' => $usersPoints,
            'hour' => $hour,
            "minut" => $minute,
            'now' => $now,
            'start' => $start,
            'end' => $end,

        ]);
        return response()->json([
            'refresh_time' => $dayStartAt,
            'range' => [
                'from' => $start->toDateTimeString(),
                'to' => $end->toDateTimeString(),
            ],
            'user_info' => $usersPoints,
            'data' => $topUsers,
        ]);
    }
    public function viaPromocode(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        Log::info("user", ['user' => $user]);
        $req = $request->validated();
        $data = $this->viaPromocodeService->proccess($req, $user, $id, 'telegram');
        if (!empty($data['promotion'])) {
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
    public function secretNumber(Request $request, $id)
    {

        $user = $request['auth_user'];
        $req = $request->validate([
            'secret_number' => ['required', 'integer', 'min:2'], // number va 1 dan katta
            'lang' => ['required', 'string', 'in:uz,ru,kr,en']
        ]);
        $data = $this->secretNumberService->proccess($req, $user, $id);
        Log::info("data", ['data' => $data]);
        return response()->json([
            'success' => $data['success'],
            'status' => $data['status'],
            'message' => $data['message'],
            'points' => $data['points']??null,
            'errors' => null,
        ],$data['code']);
    }
    public function viaReceipt(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        Log::info("user", ['user' => $user]);
        $req = $request->validated();
        $data = $this->scraper->scrapeReceipt($req);
        Log::info("Sccreppoing data", ['data' => $data]);
        // DB::table('sales_receipts')->truncate();

        $existing = SalesReceipt::where('chek_id', $data['chek_id'])->first();

        if ($existing) {
            Log::warning("❗ Receipt already exists", ['chek_id' => $data['chek_id']]);
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
    public function showAjaxData(Request $request, $id)
    {
        $user = $request['auth_user'];
        $req = $request->validate([
            'day_start_at' => ['required', 'string'], // number va 1 dan katta
        ]);
        [$hour, $minute] = explode(':', $req['day_start_at']);
        $now = now();
        $start = $now->copy()->setTime($hour, $minute);
        if ($now->lt($start)) {
            $start->subDay();
        }
        $end = $start->copy()->addDay();
        // $start = $now->copy()->subWeek(); // bir hafta oldin
        // $start->setTime($hour, $minute);   // start vaqtini dayStartAt ga sozlash

        // $end = $now->copy();
        // $start = $start->copy()->subHours(5);
        // $end = $end->copy()->subHours(5);
        // Foydalanuvchi ballarini faqat shu oraliqda hisoblaymiz
        $usersPoints = EncouragementPoint::getUserTotalAndRank(
            $user['id'],
            ['referral_start', 'referral_register', 'secret_number'],
            "Noma'lum user",
            $start,
            $end
        );
        Log::info("ahowAjaxDAta", [
            'user' => $user,
            'all_points' => EncouragementPoint::getUserTotalPoints($user['id'], ['referral_start', 'referral_register', 'secret_number']),
            'user_poinst' => $usersPoints,
            'hour'=>$hour,
            "minut"=>$minute,
            'now'=>$now,
            'start'=>$start,
            'end'=>$end
        ]);
        return response()->json(
            data: [
                'all_points'=> EncouragementPoint::getUserTotalPoints($user['id'], ['referral_start', 'referral_register', 'secret_number']),
                'today_poinst'=>$usersPoints['total_points'],
            ]
        );
    }
}
