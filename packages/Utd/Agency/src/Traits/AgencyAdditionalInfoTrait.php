<?php

namespace Utd\Agency\Traits;

use Utd\Agency\Entities\AdditionalInfo;


trait AgencyAdditionalInfoTrait
{

    public function additionalInfo()
    {
        
        return $this->hasOne(AdditionalInfo::class, 'agency_id');
    }
}
