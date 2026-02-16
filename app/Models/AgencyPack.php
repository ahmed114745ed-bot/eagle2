<?php

namespace App\Models;



if (class_exists("Utd\\Agency\\Helpers\\AgencyModelsHelper") && $agencyClass = \\Utd\\Agency\\Helpers\\AgencyModelsHelper::getAgencyPackClass()) {
    class_alias($agencypackClass, __NAMESPACE__ . '\AgencyPack');
}
