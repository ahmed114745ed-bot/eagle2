<?php

namespace App\Models;

use Utd\Agency\Helpers\AgencyModelsHelper;

if ($agencySalaryClass = AgencyModelsHelper::getAgencySalaryClass()) {
    class_alias($agencySalaryClass, __NAMESPACE__ . '\AgencySallary');
}
