<?php

namespace App\Models;



if (class_exists("Utd\\Agency\\Helpers\\AgencyModelsHelper") && $agencyClass = \\Utd\\Agency\\Helpers\\AgencyModelsHelper::getAgencymAngerLinkClass()) {
    class_alias($agencymangerlinkClass, __NAMESPACE__ . '\AgencymAngerLink');
}
