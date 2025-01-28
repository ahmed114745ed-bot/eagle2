<?php

namespace App\Tik\Repositories;

use App\Models\UserVip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;


/**
 *@property UserVip $model
 */
class UserVipRepository extends AbstractRepository
{

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new UserVip());
    }

    public function findByUserId($userId)
    {
        return $this->model->where('user_id', $userId)->orderBy('expire', 'DESC')->first();
    }

    public function deleteExpireUserVip()
    {
        $this->model->query()->where('expire', '!=', 0)->where('expire', '<', Carbon::now()->timestamp)->delete();
        return true;
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function getByUserId($userId): Collection
    {
        return $this->model->where("user_id", $userId)->get();
    }

    public function findByIdWithOVip($id)
    {
        return $this->model->with("OVip")->has("OVip")->find($id);
    }

    public function updateIsUsedForUser($userId)
    {
        return $this->model->where('user_id', $userId)->update(['is_used' => 0]);
    }
    public function updateIsUsed($userVip, $isUsed)
    {
        $userVip->is_used = $isUsed;
        $this->updateUserVip($userVip);
    }
    public function updateNumUsed($userVip)
    {
        $userVip->num_used += 1;
        $this->updateUserVip($userVip);
    }

    public function updateIsUsedWithNum($userVip, $isUsed)
    {
        $this->updateNumUsed($userVip);
        $this->updateIsUsed($userVip, $isUsed);
    }

    public function updateUserVip($userVip)
    {
        $userVip->save();
        return true;
    }

    public function findByUserLevel($userId, $level, $vipId)
    {
        return $this->model->where('user_id', $userId)->where('level', $level)->where('vip_id', $vipId)->where('expire', '!=', 0)->where('expire', '<', Carbon::now()->timestamp)->first();
    }
}
