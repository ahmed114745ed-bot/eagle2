<?php

namespace App\Tik\Repositories;

use App\Models\UserSallary;





class UserSalaryRepository extends AbstractRepository
{

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new UserSallary());
    }

    public function findByUserId($userId)
    {
        return $this->model->query()->where('user_id', $userId)->orderByDesc('id')->first();
    }

    public function incrementCutAmount($userId, $usd)
    {
        $userSalary = $this->findByUserId($userId);
        $userSalary->increment('cut_amount', (int)$usd);
        return true;
    }
}
