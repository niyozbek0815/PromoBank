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
    ) {}

    public function index()
    {
        $cacheKey = 'promotions:platform:mobile:page:' . request('page', 1);
        $ttl      = now()->addMinutes(5); // 5 daqiqa kesh
        Cache::store('redis')->forget($cacheKey);
        $promotions = Cache::store('redis')->remember($cacheKey, $ttl, function () {
            return $this->promotionRepository->getAllPromotionsForMobile();
        });
        return $this->successResponse(PromotionResource::collection($promotions), "success");
    }
    public function viaPromocode(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        $req  = $request->validated();
        $data = $this->viaPromocodeService->proccess($req, $user, $id,'mobile');
        if (! empty($data['promotion'])) {
            return $this->errorResponse('Promotion not found.', ['token' => ['Promotion not found.']], 404);
        }
        // Log::info('Log data', ['data' => $data]);
        $status = $data['status'] ?? null;
        $message = $data['message'] ?? null;

        if (in_array($status, ['claim', 'invalid'], true)) {
            return $this->errorResponse(
                $message ,
                ['promocode' => [$message ]],
                422
            );
        }

        return $this->successResponse(
            $data,
            $message
        );
    }

    public function listParticipationHistory(Request $request, $promotionId)
    {
        $user       = $request['auth_user'];
        $promo_user = PromoCodeUser::with([
            'promoCode:promocode,id',
            'receipt:id,chek_id,name,created_at',
            'platform:id,name',
            'promotionProduct:id,name',
            'prize:id,name',
        ])
        ->where('promotion_id', $promotionId)
            ->where('user_id', $user['id'])
            ->orderByDesc('id')
            ->get();
        return $this->successResponse(PromoHistoryRecource::collection($promo_user), "success");
    }
}
