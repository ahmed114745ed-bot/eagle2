<?php

namespace App\Tik\Repositories;

use Modules\AgencyApp\Entities\AgencyUserJob;


class AgencyUserJobRepository extends AbstractRepository
{

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new AgencyUserJob());
    }

    public function findByUserId($userId)
    {
        return $this->model->where('user_id', $userId)->where('type', 'requestManger')->first();
    }
}