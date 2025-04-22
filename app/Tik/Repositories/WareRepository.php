<?php

namespace App\Tik\Repositories;

use App\Models\Ware;



class WareRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new Ware());
    }


    public function getDressWare($id)
    {
        return $this->model->where(['id' => $id])->first();
    }

    public function getWithType($type, $level)
    {
        return $this->model->query()->where('get_type', 1)
            ->where('type', $type)
            ->select('id', 'img2')
            ->where('level', $level)
            ->first();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function checkWare($type)
    {
        return $this->model->where('type', $type)->exists();
    }
    public function all($userId, $type)
    {
        $wares = $this->model->query()
            ->where('enable', 1)
            ->whereIn('get_type', [4, 6])
            ->where('type', $type);
        if ($type == 25) {

            $wares = $wares->isNotUsedInPacks()->showUserCustom($userId);
        }
        return $wares->get();
    }

    public function getById($wareId)
    {
        return $this->model->query()->where('id', $wareId)
            ->where('enable', 1)->whereIn('get_type', [4, 6])->first();
    }

    public function getWaresByConditions($vipLevel, array $types, array $ids)
    {
        return $this->model
            ->where(['get_type' => 1, 'enable' => 1])
            ->where('level', '<=', $vipLevel)
            ->whereIn('type', $types)
            ->whereNotIn('id', $ids)
            ->selectRaw('id,type,expire')
            ->get();
    }

    public function countWareByLevel($level)
    {
        return $this->model->query()->where('get_type', 1)->where('enable', 1)->where('level', $level)->where('is_active_for_vip', 1)->count();
    }

    public function getOVip($levels = [], $types = [])
    {
        return $this->model->query()->where('get_type', 1)->whereIn('type', $types)->whereIn('level', $levels)/*->where('enable', true)*/->get();
    }

    public function getOVipNew($levels = [], $types = [])
    {
        return $this->model->query()->where('get_type', 1)->whereIn('type', $types)->whereIn('level', $levels)->where('enable', true)->get();
    }

    public function notActive($level)
    {
        return  $this->model->where('level', $level)->update([
            'is_active_for_vip' => false
        ]);
    }

    public function updateActiveWithType($type, $level)
    {
        return $this->model->where('type', $type)->where('level', $level)->update([
            'is_active_for_vip' => true
        ]);
    }

    public function getByTypeAndLevel($type, $level)
    {
        return $this->model->where(['type' => $type, 'get_type' => 1, 'level' => $level])->get();
    }

    public function findByTypeAndLevel($typePrivilege, $levelOvip)
    {
        return $this->model->where(['type' => $typePrivilege, 'get_type' => 1, 'level' => $levelOvip])->first();
    }

    public function allWares($page, $perPage)
    {
        return $this->model->whereNot('get_type', 1)->paginate($perPage, ['*'], 'page', $page);
    }
    public function profile_frame_wares($page, $perPage)
    {
        return $this->model->where('get_type', 1)->where('type', 28)->orderByDesc('is_active_for_vip')->select('id', 'img2', 'level', 'image_type','half_image_profile')->paginate($perPage, ['*'], 'page', $page);
    }
    public function giftOVip($level, $type)
    {
        return $this->model->where('level', $level)->where('get_type', 1)->where('type', $type)->where('is_active_for_vip', 1)->first();
    }
}
