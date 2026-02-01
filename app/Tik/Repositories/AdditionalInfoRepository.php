<?php

namespace App\Tik\Repositories;

use Utd\Agency\Entities\AdditionalInfo;

class AdditionalInfoRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new AdditionalInfo());
    }

    public function findByAgencyId($agencyId)
    {
        return $this->model->where('agency_id', $agencyId)->first();
    }
}
