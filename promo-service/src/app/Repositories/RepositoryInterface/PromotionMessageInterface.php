<?php

namespace App\Repositories\RepositoryInterface;

interface PromotionMessageInterface
{
    public function getMessageForPromotion($promotionId, $platform, $type);
}
