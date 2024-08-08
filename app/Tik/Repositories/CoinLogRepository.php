<?php

namespace App\Tik\Repositories;


use App\Models\CoinLog;

class CoinLogRepository extends AbstractRepository
{

   
    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new CoinLog());
    }

    public function getCoinsByUserId($userId)
    {
        return $this->model->where('user_id', $userId)->orderByDesc('id')->get();
    }
}
