<?php

namespace App\Tik\Repositories;

use App\Models\Vip;

class VipRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Vip());
    }

    public function getByType($type)
    {
        return $this->model->where('type', $type)->paginate(15);
    }
}