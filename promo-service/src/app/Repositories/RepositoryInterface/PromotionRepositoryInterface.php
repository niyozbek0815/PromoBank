<?php

namespace App\Repositories\RepositoryInterface;

interface PromotionRepositoryInterface
{
    public function getAllPromotionsForMobile();
    public function getPromotionByIdforViaPromocode($id);
}
