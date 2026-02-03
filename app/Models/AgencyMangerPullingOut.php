<?php

namespace App\Models;

use Utd\Agency\Helpers\AgencyModelsHelper;

if ($agencymangerpullingoutClass = AgencyModelsHelper::getAgencyMangerPullingOutClass()) {
    class_alias($agencymangerpullingoutClass, __NAMESPACE__ . '\AgencyMangerPullingOut');
}
