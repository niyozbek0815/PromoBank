<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPromocodeRequest;
use App\Http\Resources\PromotionResource;
use App\Models\Platform;
use App\Models\Prize;
use App\Models\PrizeCategory;
use App\Models\PromoCode;
use App\Models\PromoCodeUser;
use App\Models\Promotions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PromoController extends Controller
{
    public function index()
    {

        $cacheKey = 'promotions:platform:mobile:page:' . request('page', 1);
        $ttl = now()->addMinutes(5); // 5 daqiqa kesh

        $promo = Cache::store('redis')->remember($cacheKey, $ttl, function () {
            return Promotions::whereHas('platforms', function ($query) {
                $query->where('name', 'mobile');
            })
                ->with([
                    'media',
                    'company:id,name,title,region,address',
                    'company.media',
                    'company.socialMedia.type',
                    'participationTypes.participationType'
                ])
                ->select('id', 'company_id', 'name', 'title', 'description', 'start_date', 'end_date')
                ->paginate(10);
        });
        return $this->successResponse(['promotions' => PromotionResource::collection($promo)], "success");
    }
    public function viaPromocode(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        $req = $request->validated();

        // Redis caching for platform ID
        $platformId = Cache::store('redis')->remember('platform:mobile:id', now()->addMinutes(60), function () {
            return Platform::where('name', 'mobile')->value('id');
        });

        // Promotion mavjudligini tekshirish
        $promotion = Promotions::whereHas('platforms', function ($query) {
            $query->where('name', 'mobile');
        })
            ->whereHas('participationTypes.participationType', function ($query) {
                $query->whereIn('slug', ['text_code', 'qr_code']);
            })->with([
                'media',
            ])
            ->select('id', 'is_prize') // faqat kerakli ustun
            ->find($id);

        if (!$promotion) {
            return $this->errorResponse('Promotion not found.', 'Promotion not found.', 404);
        }

        // Promocode validatsiyasi
        $promo = PromoCode::where('promotion_id', $id)
            ->where('promocode', $req['promocode'])
            ->first();
        // return $this->successResponse(['promotions' => $promo], "success");
        if (!$promo) {
            return $this->errorResponse('Promocode not found.', 'Promocode not found.', 404);
        }

        if ($promo->is_used) {
            return $this->errorResponse('Promocode avval foydalanilgan.', 'Promocode avval foydalanilgan.', 422);
        }
        if ($promotion->is_prize) {
            // $categoryNames = PrizeCategory::whereHas('prizes', function ($query) use ($id) {
            //     $query->where('promotion_id', $id);
            // })->pluck('name');
            $prize = Prize::whereHas('prizePromo', function ($q) use ($promo) {
                $q->where('promo_code_id', $promo->id);
            })->first();

            if ($prize) {
                return $this->successResponse(['data' => "Prize message"], 'Success');
            }
            
        } else {
            $promoUser = PromoCodeUser::create([
                'promo_code_id' => $promo->id,
                'user_id'       => $user['id'],
                'platform_id'   => $platformId,
            ]);
        }

        // Promocode foydalanuvchiga biriktiriladi


        // Promocode yangilash
        $promo->update([
            'is_used' => true,
            'used_at' => now(),
        ]);

        return $this->successResponse(['data' => $promoUser], 'Success');
    }

    public function viaReceipt(Request $request, $promotionId)
    {
        return $this->successResponse(['promotions' => "success2"], "success");
    }

    public function checkStatus(Request $request, $promotionId)
    {
        return $this->successResponse(['promotions' => "success3"], "success");
    }
}
