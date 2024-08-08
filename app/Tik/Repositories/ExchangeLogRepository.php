<?php

namespace App\Tik\Repositories;


use App\Models\ExchangeLog;

class ExchangeLogRepository extends AbstractRepository
{

    
    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new ExchangeLog());
    }

    public function getExchanges($userId, $type)
    {
        return $this->model->where('user_id', $userId)->where('type', $type)
            ->selectRaw('id,user_id,diamonds,value as coins,operation_no,status,created_at as date')
            ->get();
    }
}
