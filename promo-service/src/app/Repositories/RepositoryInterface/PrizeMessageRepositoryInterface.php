<?php

namespace App\Repositories\RepositoryInterface;

interface PrizeMessageRepositoryInterface
{
    public function getMessageForPrize($promotionId, $platform, $type);
}
