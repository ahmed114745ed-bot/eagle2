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

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function listVip($search)
    {
        return $this->model->query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")->orWhere('id', $search);
            })->select('id', 'name', 'img1')->get();
    }
}
