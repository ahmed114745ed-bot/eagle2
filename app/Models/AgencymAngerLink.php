<?php

namespace App\Models;

use Utd\Agency\Helpers\AgencyModelsHelper;

if ($agencymangerlinkClass = AgencyModelsHelper::getAgencymAngerLinkClass()) {
    class_alias($agencymangerlinkClass, __NAMESPACE__ . '\AgencymAngerLink');
}
