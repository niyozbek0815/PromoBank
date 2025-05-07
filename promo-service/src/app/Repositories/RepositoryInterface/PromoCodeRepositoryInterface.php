<?php

namespace App\Repositories\RepositoryInterface;

interface PromoCodeRepositoryInterface
{
    public function getPromoCodeByPromotionIdAndByPromocode($promotionId, $promocode);
}
