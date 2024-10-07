<?php

namespace App\Tik\Repositories;

use App\Models\CoreWallets;

class CoreWalletsRepository extends AbstractRepository
{
    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new CoreWallets());
    }

    public function all()
    {
        return $this->model->get();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }
}