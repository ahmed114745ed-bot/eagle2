<?php

namespace App\Models;



if (class_exists("Utd\\Agency\\Helpers\\AgencyModelsHelper") && $agencyClass = \\Utd\\Agency\\Helpers\\AgencyModelsHelper::getAgencyMangerPullingOutClass()) {
    class_alias($agencymangerpullingoutClass, __NAMESPACE__ . '\AgencyMangerPullingOut');
}
