<?php

namespace App\Jobs;

use App\Models\PromoCode;
use App\Models\PromoCodeUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
            PromoCodeUser::create([
                'promo_code_id' => is_array($this->promoCodeId) ? null : $this->promoCodeId,
                'user_id' => is_array($this->userId) ? null : $this->userId,
                'platform_id' => is_array($this->platformId) ? null : $this->platformId,
                'receipt_id' => is_array($this->receiptId) ? null : $this->receiptId,
                'promotion_product_id' => is_array($this->promotionProductId) ? null : $this->promotionProductId,
                'prize_id' => is_array($this->prizeId) ? null : $this->prizeId,
                'sub_prize_id' => is_array($this->subPrizeId) ? null : $this->subPrizeId,
                'promotion_id' => is_array($this->promotionId) ? null : $this->promotionId,
            ]);

            if ($this->promoCodeId !== null) {
                PromoCode::where('id', $this->promoCodeId)->update([
                    'is_used' => true,
                    'used_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            logger()->error('PromoCodeConsumeJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
