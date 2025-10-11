<?php

namespace App\Repositories;

use App\Models\PrizeMessage;
use App\Repositories\RepositoryInterface\PrizeMessageRepositoryInterface;

class PrizeMessageRepository implements PrizeMessageRepositoryInterface
{
    protected $model;

    public function __construct(PrizeMessage $model)
    {
        $this->model = $model;
    }
    public function getMessageForPrize($prize, $platform, $type)
    {
        return $this->model->getMessageFor($prize, $platform, $type);
    }
}
