<?php

namespace App\Tik\Repositories;

use App\Models\AdminUser;


class AdminUsersRepository extends AbstractRepository
{

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new AdminUser());
    }

    public function all()
    {
        return $this->model->with('user', 'managerAgencies')->get();
        
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }
}
