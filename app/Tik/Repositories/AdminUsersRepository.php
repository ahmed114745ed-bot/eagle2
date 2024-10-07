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
        return $this->model->whereHas('roles', fn($q) => $q->where('slug', 'like', '%_genc%_anager%'))
        ->where('app_id','!=',0)->with(['user' => fn ($q) => $q->withCount('agencies')])->with([ 'managerAgencies' => fn($q) => $q->withSum('agencySalaries as total_salaries', 'sallary')])->get();
        
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }
}
