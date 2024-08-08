<?php

namespace App\Tik\Repositories;

use App\Models\OVip;

class OvipRepository extends AbstractRepository
{



    public function __construct()
    {
        parent::__construct(new OVip());
    }

    public function getOvip()
    {
        return $this->model->query()->select('id', 'name', 'level', 'img')->get();
    }
}