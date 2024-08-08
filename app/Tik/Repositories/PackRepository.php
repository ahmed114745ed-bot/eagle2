<?php

namespace App\Tik\Repositories;

use App\Models\Pack;

class PackRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new Pack());
    }


    public function findByUserId($userId, $type)
    {
        return $this->model->query()
            ->where('type', $type)
            ->where(function ($q) {
                $q->where('expire', '>=', time())->orWhere('expire', '=', 0);
            })
            ->where('user_id', $userId)
            ->where('use_num', '>', 0)
            ->orderByDesc('id')
            ->first();
    }

    public function checkPack($userId, $type, $isAvailable)
    {
        return $this->model->query()->where('user_id', $userId)->where('type', $type)->where('is_used', !$isAvailable)->exists();
    }

    public function changeAvailabilityAllPack($userId,$type,$isAvailable)
    {
        $this->model->query()->where('user_id', $userId)->where('type', $type)->update(['is_used' => $isAvailable]);
        return true;
    }
}
