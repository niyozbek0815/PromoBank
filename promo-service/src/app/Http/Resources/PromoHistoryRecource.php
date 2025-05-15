<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoHistoryRecource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'promo_code' => $this->promoCode->promocode ?? null,
            'platform' => $this->platform->name ?? null,
            'promotion_product' => $this->promotionProduct->name ?? null,
            'prize' => $this->prize->name ?? null,
            // 'sub_prize_id' => $this->sub_prize_id,
            'receipt' => $this->receipt ? [
                'id' => $this->receipt_id,
                'chek_id' => $this->receipt->chek_id,
                'shop_name' => $this->receipt->name,
                'created_at' => $this->receipt->created_at,
            ] : null,
        ];
    }
}
