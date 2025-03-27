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

    public function getByLevels($levelsList, $type)
    {
        return $this->model->query()->whereIn('level', $levelsList)
            ->where('type', $type)->select('img', 'level')->get();
    }

    public function badgesVip($type)
    {
        return $this->model->where(function ($query) {
            $query->whereRaw('MOD(level + 1, 10) = 1')->orWhere('level', 1);
        })->where('type', $type)->where('level', "!=", 0)->orderBy('level')->get();
    }

    public function findByLevel($level, $type)
    {
        return $this->model->where("level", $level)->where('type', $type)->orderByDesc('level')->first();
    }

    public function nextLevel($level, $type)
    {
        return $this->model->where("type", $type)->where("level", ">", $level)->orderBy('id')->first();
    }

    public function findByType($type)
    {
        return $this->model->where("type", $type)->orderBy('level')->first();
    }
}
