<?php

namespace App\Models;



if (class_exists("Utd\\Agency\\Helpers\\AgencyModelsHelper") && $agencyClass = \\Utd\\Agency\\Helpers\\AgencyModelsHelper::getAgencyMangerDeletedClass()) {
    class_alias($agencymangerdeletedClass, __NAMESPACE__ . '\AgencyMangerDeleted');
}
