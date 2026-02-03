<?php

namespace App\Models;

use Utd\Agency\Helpers\AgencyModelsHelper;

if ($agencyClass = AgencyModelsHelper::getAgencyClass()) {
    class_alias($agencyClass, __NAMESPACE__ . '\Agency');
}
