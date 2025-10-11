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
            'user_id' => $this->user_id,
            'promo_code' => $this->promoCode->promocode ?? null,
            'platform' => $this->platform->name ?? null,
            'promotion_product' => $this->promotion_product_id,
            'prize' => $this->prize->name ?? null,

            'manual_message' => is_null($this->prize)
                ? [
                    'uz' => "Siz aksiyada sovrin yutish imkoniyatini qo‘lga kiritdingiz.",
                    'ru' => "Вы получили возможность выиграть приз в акции.",
                    'en' => "You have earned a chance to win a prize in the promotion.",
                    'kr' => "Сиз акцияда соврин ютуб олиш имкониятини қўлга киритдингиз."
                ]
                : null,

            'receipt' => $this->receipt ? [
                'id' => $this->receipt_id,
                'chek_id' => $this->receipt->chek_id,
                'shop_name' => $this->receipt->name,
                'created_at' => $this->receipt->created_at,
            ] : null,

            'created_at' => $this->created_at
        ];
    }
}
