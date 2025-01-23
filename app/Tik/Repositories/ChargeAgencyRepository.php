<?php

namespace App\Tik\Repositories;

use Modules\SalaryTransaction\Entities\ChargeAgency;

class ChargeAgencyRepository extends AbstractRepository
{

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new ChargeAgency());
    }

    public function all($agencyId)
    {
        return $this->model->Select('agency_id')->groupBy('agency_id')->when(isset($agencyId), function ($query) use ($agencyId) {
            $query->where('agency_id', $agencyId);
        })->get();
    }
}
