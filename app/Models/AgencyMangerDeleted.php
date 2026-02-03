<?php

namespace App\Models;

use Utd\Agency\Helpers\AgencyModelsHelper;

if ($agencymangerdeletedClass = AgencyModelsHelper::getAgencyMangerDeletedClass()) {
    class_alias($agencymangerdeletedClass, __NAMESPACE__ . '\AgencyMangerDeleted');
}
