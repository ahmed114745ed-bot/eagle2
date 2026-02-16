<?php
namespace App\Models;
if (class_exists('Utd\Agency\Helpers\AgencyModelsHelper') && $agencyClass = \Utd\Agency\Helpers\AgencyModelsHelper::getAgencyJoinRequestClass()) {
    class_alias($agencyClass, __NAMESPACE__ . '\AgencyJoinRequest');
}
