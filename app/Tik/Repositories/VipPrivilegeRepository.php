<?php

namespace App\Tik\Repositories;

use App\Models\VipPrivilege;

class VipPrivilegeRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new VipPrivilege());
    }

    public function all()
    {
       return $this->model->query()->get();
    }
}