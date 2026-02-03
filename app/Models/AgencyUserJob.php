<?php

namespace App\Models;

use Utd\Agency\Helpers\AgencyModelsHelper;

if ($agencyUserJobClass = AgencyModelsHelper::getAgencyUserJobClass()) {
    class_alias($agencyUserJobClass, __NAMESPACE__ . '\AgencyUserJob');
}
