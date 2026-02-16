<?php

namespace App\Models;



if (class_exists("Utd\\Agency\\Helpers\\AgencyModelsHelper") && $agencyClass = \\Utd\\Agency\\Helpers\\AgencyModelsHelper::getAgencyMangLinkClass()) {
    class_alias($agencymanglinkClass, __NAMESPACE__ . '\AgencyMangLink');
}
