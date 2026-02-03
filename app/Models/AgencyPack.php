<?php

namespace App\Models;

use Utd\Agency\Helpers\AgencyModelsHelper;

if ($agencypackClass = AgencyModelsHelper::getAgencyPackClass()) {
    class_alias($agencypackClass, __NAMESPACE__ . '\AgencyPack');
}
