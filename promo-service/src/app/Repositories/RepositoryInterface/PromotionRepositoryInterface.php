<?php

namespace App\Repositories\RepositoryInterface;

interface PromotionRepositoryInterface
{
    public function getAllPromotionsForMobile();
    public function getPromotionByIdforVia($id, array $slug);
}
