<?php

namespace App\Repositories;

use App\Models\PromotionMessage;
use App\Repositories\RepositoryInterface\PromotionMessageInterface;

class PromotionMessageRepository implements PromotionMessageInterface
{
    protected $model;

    public function __construct(PromotionMessage $model)
    {
        $this->model = $model;
    }
    public function getMessageForPromotion($promotionId, $platform, $type)
    {
        return $this->model->getMessageForPromotionId($promotionId, 'mobile', $type);
    }
}
