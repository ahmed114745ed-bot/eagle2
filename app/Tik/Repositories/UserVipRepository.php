<?php

namespace App\Tik\Repositories;

use App\Models\UserVip;


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

}