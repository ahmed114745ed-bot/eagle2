<?php

namespace Utd\Agency\Helpers;

use Utd\Agency\Entities\Agency;
use Utd\Agency\Entities\AgencyJoinRequest;
use Utd\Agency\Entities\AgencySalary;
use Utd\Agency\Entities\AgencyUserJob;
use Utd\Agency\Entities\AgencyMangerDeleted;
use Utd\Agency\Entities\AgencymAngerLink;
use Utd\Agency\Entities\AgencyMangerPullingOut;
use Utd\Agency\Entities\AgencyMangLink;
use Utd\Agency\Entities\AgencyPack;

class AgencyModelsHelper
{
    /**
     * Get Agency model class
     */
    public static function getAgencyClass(): ?string
    {
        return class_exists(Agency::class) ? Agency::class : null;
    }

    /**
     * Get AgencyJoinRequest model class
     */
    public static function getAgencyJoinRequestClass(): ?string
    {
        return class_exists(AgencyJoinRequest::class) ? AgencyJoinRequest::class : null;
    }

    /**
     * Get AgencySalary model class
     */
    public static function getAgencySalaryClass(): ?string
    {
        return class_exists(AgencySalary::class) ? AgencySalary::class : null;
    }

    /**
     * Get AgencyUserJob model class
     */
    public static function getAgencyUserJobClass(): ?string
    {
        return class_exists(AgencyUserJob::class) ? AgencyUserJob::class : null;
    }

    /**
     * Get AgencyMangerDeleted model class
     */
    public static function getAgencyMangerDeletedClass(): ?string
    {
        return class_exists(AgencyMangerDeleted::class) ? AgencyMangerDeleted::class : null;
    }

    /**
     * Get AgencymAngerLink model class
     */
    public static function getAgencymAngerLinkClass(): ?string
    {
        return class_exists(AgencymAngerLink::class) ? AgencymAngerLink::class : null;
    }

    /**
     * Get AgencyMangerPullingOut model class
     */
    public static function getAgencyMangerPullingOutClass(): ?string
    {
        return class_exists(AgencyMangerPullingOut::class) ? AgencyMangerPullingOut::class : null;
    }

    /**
     * Get AgencyMangLink model class
     */
    public static function getAgencyMangLinkClass(): ?string
    {
        return class_exists(AgencyMangLink::class) ? AgencyMangLink::class : null;
    }

    /**
     * Get AgencyPack model class
     */
    public static function getAgencyPackClass(): ?string
    {
        return class_exists(AgencyPack::class) ? AgencyPack::class : null;
    }

    /**
     * Check if Agency package is available
     */
    public static function isPackageAvailable(): bool
    {
        return class_exists(Agency::class);
    }

    /**
     * Get all available model classes
     */
    public static function getAllModels(): array
    {
        return array_filter([
            'Agency' => self::getAgencyClass(),
            'AgencyJoinRequest' => self::getAgencyJoinRequestClass(),
            'AgencySalary' => self::getAgencySalaryClass(),
            'AgencyUserJob' => self::getAgencyUserJobClass(),
            'AgencyMangerDeleted' => self::getAgencyMangerDeletedClass(),
            'AgencymAngerLink' => self::getAgencymAngerLinkClass(),
            'AgencyMangerPullingOut' => self::getAgencyMangerPullingOutClass(),
            'AgencyMangLink' => self::getAgencyMangLinkClass(),
            'AgencyPack' => self::getAgencyPackClass(),
        ]);
    }
}
