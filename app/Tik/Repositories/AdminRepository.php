<?php

namespace App\Tik\Repositories;

use App\Models\Admin;


class AdminRepository extends AbstractRepository
{

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new Admin());
    }

    public function findById($userId)
    {
     return $this->model->find($userId);
    }
}
