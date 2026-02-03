<?php

namespace App\Models;

use Utd\Agency\Helpers\AgencyModelsHelper;

if ($agencymanglinkClass = AgencyModelsHelper::getAgencyMangLinkClass()) {
    class_alias($agencymanglinkClass, __NAMESPACE__ . '\AgencyMangLink');
}
