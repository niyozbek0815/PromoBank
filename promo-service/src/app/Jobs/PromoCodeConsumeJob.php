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
    protected $promotionId;

    public function __construct(
        $promoCodeId = null,
        $userId,
        $platformId,
        $receiptId = null,
        $promotionProductId = null,
        $prizeId = null,
        $subPrizeId = null,
        $promotionId
    ) {
        $this->promoCodeId = $promoCodeId;
        $this->userId = $userId;
        $this->platformId = $platformId;
        $this->receiptId = $receiptId;
        $this->promotionProductId = $promotionProductId;
        $this->prizeId = $prizeId;
        $this->subPrizeId = $subPrizeId;
        $this->promotionId = $promotionId;
    }

    public function handle()
    {
        try {
            logger()->info('PromoCodeConsumeJob started', [
                'promo_code_id' => $this->promoCodeId,
                'user_id' => $this->userId,
                'platform_id' => $this->platformId,
                'receipt_id' => $this->receiptId,
                'promotion_product_id' => $this->promotionProductId,
                'prize_id' => $this->prizeId,
                'sub_prize_id' => $this->subPrizeId,
                'promotion_id' => $this->promotionId,
            ]);

            // Faqat kerakli qiymatlar bilan to'ldiriladi, null'lar saqlanadi
            PromoCodeUser::create([
                'promo_code_id' => $this->promoCodeId,
                'user_id' => $this->userId,
                'platform_id' => $this->platformId,
                'receipt_id' => $this->receiptId,
                'promotion_product_id' => $this->promotionProductId,
                'prize_id' => $this->prizeId,
                'sub_prize_id' => $this->subPrizeId,
                'promotion_id' => $this->promotionId,
            ]);

            logger()->info('PromoCodeUser record created');

            if ($this->promoCodeId !== null) {
                PromoCode::where('id', $this->promoCodeId)->update([
                    'is_used' => true,
                    'used_at' => now(),
                ]);
                logger()->info('PromoCode marked as used', ['promo_code_id' => $this->promoCodeId]);
            }
        } catch (\Throwable $e) {
            logger()->error('PromoCodeConsumeJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
