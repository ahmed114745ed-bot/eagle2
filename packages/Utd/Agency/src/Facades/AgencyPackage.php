<?php

namespace Utd\Agency\Facades;

use Illuminate\Support\Facades\Facade;
use Utd\Agency\Helpers\AgencyModelsHelper;

/**
 * Agency Package Facade
 * 
 * Provides safe access to Agency package models
 * 
 * @method static string|null getAgencyClass()
 * @method static string|null getAgencyJoinRequestClass()
 * @method static string|null getAgencySalaryClass()
 * @method static string|null getAgencyUserJobClass()
 * @method static string|null getAgencyMangerDeletedClass()
 * @method static string|null getAgencymAngerLinkClass()
 * @method static string|null getAgencyMangerPullingOutClass()
 * @method static string|null getAgencyMangLinkClass()
 * @method static string|null getAgencyPackClass()
 * @method static bool isPackageAvailable()
 * @method static array getAllModels()
 */
class AgencyPackage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AgencyModelsHelper::class;
    }
}
