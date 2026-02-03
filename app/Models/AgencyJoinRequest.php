<?php

namespace App\Models;

use Utd\Agency\Helpers\AgencyModelsHelper;

if ($agencyJoinRequestClass = AgencyModelsHelper::getAgencyJoinRequestClass()) {
    class_alias($agencyJoinRequestClass, __NAMESPACE__ . '\AgencyJoinRequest');
}
