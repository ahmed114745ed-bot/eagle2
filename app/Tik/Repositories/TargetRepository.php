<?php

namespace App\Tik\Repositories;

use App\Models\Target;


class TargetRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Target());
    }

    public function getByUsd($target)
    {
        return $this->model->where('usd', '<', $target)->orderBy('usd', 'desc')->first();
    }

    public function getByDiamonds($diamond)
    {
        return $this->model->query()->where('diamonds', '>', $diamond)->orderBy('diamonds')->first();
    }
}
