<?php

namespace App\Tik\Repositories;


use App\Models\Exchange;

class ExchangeRepository extends AbstractRepository
{

    
    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new Exchange());
    }

    public function getByType($type)
    {
        return $this->model->query()->where('type', $type)->orderBy('diamonds')->get();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }
}
