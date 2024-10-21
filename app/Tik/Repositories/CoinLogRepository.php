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
        dd('this');
    }

    public function getCoinsByUserId($userId, string $searchKey = null)
    {
        return $this->model::where('user_id', $userId)
            ->when($searchKey, fn($q) => $q->where('trx', 'like', $searchKey))
            ->orderByDesc('id')->get();
    }
}
