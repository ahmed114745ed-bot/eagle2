<?php

namespace App\Tik\Repositories;

use App\Models\Agency;




class AgencyRepository extends AbstractRepository
{

    
    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new Agency());
    }


    public function findAgencyByOwnerId($ownerId)
    {
        return $this->model->where('app_owner_id', $ownerId)->first();
    }

    public function findById($id)
    {
        return $this->model->where('id', $id)->first();
    }
}
