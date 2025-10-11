<?php


namespace App\Repositories;

use App\Models\PromoCode;
use App\Repositories\RepositoryInterface\PromoCodeRepositoryInterface;

class PromoCodeRepository implements PromoCodeRepositoryInterface
{
    protected $model;

    public function __construct(PromoCode $model)
    {
        $this->model = $model;
    }
    public function getPromoCodeByPromotionIdAndByPromocode($promotionId, $promocode)
    {
        return $this->model->where('promotion_id', $promotionId)
            ->where('promocode', $promocode)
            ->first();
    }
}
