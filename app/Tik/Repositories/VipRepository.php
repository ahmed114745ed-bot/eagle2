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

    public function getByLevels($levelsList,$type)
    {
        return $this->model->query()->whereIn('level', $levelsList)
        ->where('type', $type)->select('img', 'level')->get();
    }

    public function badgesVip()
    {
        return $this->model->whereRaw('MOD(level + 1, 10) = 1')->orWhere('level',1)->where('level',"!=",0)->orderBy('level')->get();
    }
}