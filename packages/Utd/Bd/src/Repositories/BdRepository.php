<?php

namespace Utd\Bd\Repositories;

use Utd\Bd\Entities\Bd;
use App\Tik\Repositories\AbstractRepository;

class BdRepository extends AbstractRepository
{

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new Bd());
    }

    public function findByAppUser($appId)
    {
        return $this->model->where('app_id', $appId)->first();
    }
}
