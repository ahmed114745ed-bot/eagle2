<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Schema;

/**
 * AgencyPackageHelper
 * 
 * مساعد للتحقق من وجود حزمة الوكالات والتعامل معها بأمان
 * Helper to check agency package existence and handle it safely
 */
class AgencyPackageHelper
{
    private static ?bool $agencyTableExists = null;
    private static ?bool $userSallariesTableExists = null;
    
    /**
     * Check if Host Agency package is installed AND agencies table exists
     */
    public static function isAgencyInstalled(): bool
    {
        // Check class exists
        $classExists = class_exists(\Utd\Agency\Entities\Agency::class) 
            || class_exists(\App\Models\Agency::class);
        
        if (!$classExists) {
            return false;
        }
        
        // Cache table check to avoid repeated queries
        if (self::$agencyTableExists === null) {
            try {
                self::$agencyTableExists = Schema::hasTable('agencies');
            } catch (\Exception $e) {
                self::$agencyTableExists = false;
            }
        }
        
        return self::$agencyTableExists;
    }
    
    /**
     * Check if user_sallaries table exists
     */
    public static function isUserSallariesTableExists(): bool
    {
        if (self::$userSallariesTableExists === null) {
            try {
                self::$userSallariesTableExists = Schema::hasTable('user_sallaries');
            } catch (\Exception $e) {
                self::$userSallariesTableExists = false;
            }
        }
        
        return self::$userSallariesTableExists;
    }

    /**
     * Check if Shipping Agency package is installed
     */
    public static function isShippingAgencyInstalled(): bool
    {
        return class_exists(\Utd\Agency\Entities\ShippingAgency::class);
    }

    /**
     * Check if AgencyApp module is enabled
     */
    public static function isAgencyAppModuleEnabled(): bool
    {
        if (!class_exists(\Nwidart\Modules\Facades\Module::class)) {
            return false;
        }

        $module = \Nwidart\Modules\Facades\Module::find('AgencyApp');
        return $module && $module->isEnabled();
    }

    /**
     * Check if SalaryTransaction module is enabled
     */
    public static function isSalaryTransactionModuleEnabled(): bool
    {
        if (!class_exists(\Nwidart\Modules\Facades\Module::class)) {
            return false;
        }

        $module = \Nwidart\Modules\Facades\Module::find('SalaryTransaction');
        return $module && $module->isEnabled();
    }

    /**
     * Get Agency model class
     */
    public static function getAgencyClass(): ?string
    {
        if (class_exists(\Utd\Agency\Entities\Agency::class)) {
            return \Utd\Agency\Entities\Agency::class;
        }

        if (class_exists(\App\Models\Agency::class)) {
            return \App\Models\Agency::class;
        }

        return null;
    }

    /**
     * Get ShippingAgency model class
     */
    public static function getShippingAgencyClass(): ?string
    {
        if (class_exists(\Utd\Agency\Entities\ShippingAgency::class)) {
            return \Utd\Agency\Entities\ShippingAgency::class;
        }

        return null;
    }

    /**
     * Get AgencyService if available
     */
    public static function getAgencyService()
    {
        if (!class_exists(\Utd\Agency\Contracts\AgencyServiceInterface::class)) {
            return null;
        }

        try {
            return app(\Utd\Agency\Contracts\AgencyServiceInterface::class);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get AgencyRepository if available
     */
    public static function getAgencyRepository()
    {
        if (!class_exists(\Utd\Agency\Contracts\AgencyRepositoryInterface::class)) {
            return null;
        }

        try {
            return app(\Utd\Agency\Contracts\AgencyRepositoryInterface::class);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Safe agency find by ID
     */
    public static function findAgency(int $id)
    {
        $agencyClass = self::getAgencyClass();
        
        if (!$agencyClass) {
            return null;
        }

        return $agencyClass::find($id);
    }

    /**
     * Safe shipping agency find by ID
     */
    public static function findShippingAgency(int $id)
    {
        $shippingClass = self::getShippingAgencyClass();
        
        if (!$shippingClass) {
            return null;
        }

        return $shippingClass::find($id);
    }

    /**
     * Get empty response for API when agency not installed
     */
    public static function emptyResponse(string $message = 'Agency feature not available'): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => null,
        ];
    }

    /**
     * Get empty collection response
     */
    public static function emptyCollectionResponse(): array
    {
        return [
            'success' => true,
            'data' => [],
            'meta' => [
                'total' => 0,
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1,
            ],
        ];
    }

    /**
     * Format agency data safely
     */
    public static function formatAgency($agency): ?array
    {
        if (!$agency) {
            return null;
        }

        return [
            'id' => $agency->id ?? null,
            'name' => $agency->name ?? '',
            'img' => $agency->img ?? '',
            'owner_id' => $agency->app_owner_id ?? null,
        ];
    }

    /**
     * Format shipping agency data safely
     */
    public static function formatShippingAgency($shippingAgency): ?array
    {
        if (!$shippingAgency) {
            return null;
        }

        return [
            'id' => $shippingAgency->id ?? null,
            'name' => $shippingAgency->name ?? '',
            'img' => $shippingAgency->img ?? '',
            'owner_id' => $shippingAgency->app_owner_id ?? null,
        ];
    }

    /**
     * Check if user belongs to any agency
     */
    public static function userHasAgency($user): bool
    {
        if (!$user || !self::isAgencyInstalled()) {
            return false;
        }

        return !empty($user->agency_id) && $user->agency_id > 0;
    }

    /**
     * Check if user owns an agency
     */
    public static function userOwnsAgency($user): bool
    {
        if (!$user || !self::isAgencyInstalled()) {
            return false;
        }

        $agencyClass = self::getAgencyClass();
        if (!$agencyClass) {
            return false;
        }

        return $agencyClass::where('app_owner_id', $user->id)->exists();
    }

    /**
     * Check if user owns a shipping agency
     */
    public static function userOwnsShippingAgency($user): bool
    {
        if (!$user || !self::isShippingAgencyInstalled()) {
            return false;
        }

        $shippingClass = self::getShippingAgencyClass();
        if (!$shippingClass) {
            return false;
        }

        return $shippingClass::where('app_owner_id', $user->id)->exists();
    }
}
