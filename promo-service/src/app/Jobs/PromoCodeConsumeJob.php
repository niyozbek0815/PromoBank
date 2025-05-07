<?php

namespace App\Jobs;

use App\Models\PromoCode;
use App\Models\PromoCodeUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PromoCodeConsumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $promoCodeId;
    protected $userId;
    protected $platformId;
    protected $receiptId;
    protected $promotionProductId;
    protected $prizeId;
    protected $subPrizeId;

    public function __construct(
        $promoCodeId,
        $userId,
        $platformId,
        $receiptId = null,
        $promotionProductId = null,
        $prizeId = null,
        $subPrizeId = null
    ) {
        $this->promoCodeId = $promoCodeId;
        $this->userId = $userId;
        $this->platformId = $platformId;
        $this->receiptId = $receiptId;
        $this->promotionProductId = $promotionProductId;
        $this->prizeId = $prizeId;
        $this->subPrizeId = $subPrizeId;
    }

    public function handle()
    {
        $data = [
            'promo_code_id'         => $this->promoCodeId,
            'user_id'               => $this->userId,
            'platform_id'           => $this->platformId,
            'receipt_id'            => $this->receiptId,
            'promotion_product_id'  => $this->promotionProductId,
            'prize_id'              => $this->prizeId,
            'sub_prize_id'          => $this->subPrizeId,
        ];

        // Faqat kerakli qiymatlar bilan to'ldiriladi, null'lar saqlanadi
        PromoCodeUser::create($data);

        PromoCode::where('id', $this->promoCodeId)->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
    }
}