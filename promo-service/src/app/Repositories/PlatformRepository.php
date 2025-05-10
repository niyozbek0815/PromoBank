<?php

namespace App\Repositories;

use App\Repositories\RepositoryInterface\PlatformRepositoryInterface;

class PlatformRepository implements PlatformRepositoryInterface
{
    protected $model;

    public function __construct(\App\Models\Platform $model)
    {
        $this->model = $model;
    }

    public function getPlatformGetId($name)
    {
        return $this->model->where('name', $name)->value('id');
    }
}
