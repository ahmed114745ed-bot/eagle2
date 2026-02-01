<?php

namespace App\Traits;

/**
 * Trait SafeAgencyRelationsTrait
 * 
 * يوفر علاقات آمنة مع الوكالات - تعمل حتى لو لم تكن حزمة الوكالات مثبتة
 * Provides safe agency relations - works even if agency package is not installed
 */
trait SafeAgencyRelationsTrait
{
    /**
     * Check if Agency package is installed
     */
    public static function isAgencyPackageInstalled(): bool
    {
        return class_exists(\Utd\Agency\Entities\Agency::class);
    }

    /**
     * Check if ShippingAgency package is installed
     */
    public static function isShippingAgencyPackageInstalled(): bool
    {
        return class_exists(\Utd\ShippingAgency\Entities\ShippingAgency::class);
    }

    /**
     * Check if AgencyApp module is installed
     */
    public static function isAgencyAppModuleInstalled(): bool
    {
        return class_exists(\Utd\Agency\Entities\AdditionalInfo::class);
    }

    /**
     * Get Agency model class if available
     */
    protected function getAgencyModelClass(): ?string
    {
        // First check package entity
        if (class_exists(\Utd\Agency\Entities\Agency::class)) {
            return \Utd\Agency\Entities\Agency::class;
        }
        
        // Fallback to App model
        if (class_exists(\App\Models\Agency::class)) {
            return \App\Models\Agency::class;
        }
        
        return null;
    }

    /**
     * Get ShippingAgency model class if available
     */
    protected function getShippingAgencyModelClass(): ?string
    {
        if (class_exists(\Utd\ShippingAgency\Entities\ShippingAgency::class)) {
            return \Utd\ShippingAgency\Entities\ShippingAgency::class;
        }
        
        return null;
    }

    /**
     * Get UsersJoinedAgency model class if available
     */
    protected function getUsersJoinedAgencyModelClass(): ?string
    {
        if (class_exists(\App\Models\UsersJoinedAgency::class)) {
            return \App\Models\UsersJoinedAgency::class;
        }
        
        return null;
    }

    /**
     * Get AgencyJoinRequest model class if available
     */
    protected function getAgencyJoinRequestModelClass(): ?string
    {
        if (class_exists(\App\Models\AgencyJoinRequest::class)) {
            return \App\Models\AgencyJoinRequest::class;
        }
        
        return null;
    }

    /**
     * Safe agency relation - returns empty relation if package not installed
     */
    public function agency()
    {
        $agencyClass = $this->getAgencyModelClass();
        
        if (!$agencyClass) {
            // Return empty relation that never matches
            return $this->belongsTo(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        
        return $this->belongsTo($agencyClass, 'agency_id');
    }

    /**
     * Safe agencies relation (for agency manager)
     */
    public function agencies()
    {
        $agencyClass = $this->getAgencyModelClass();
        
        if (!$agencyClass) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        
        return $this->hasMany($agencyClass, 'agency_manger_id');
    }

    /**
     * Safe ownAgency relation
     */
    public function ownAgency()
    {
        $agencyClass = $this->getAgencyModelClass();
        
        if (!$agencyClass) {
            return $this->hasOne(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        
        return $this->hasOne($agencyClass, 'app_owner_id', 'id');
    }

    /**
     * Safe hostAgency relation
     */
    public function hostAgency()
    {
        $agencyClass = $this->getAgencyModelClass();
        
        if (!$agencyClass) {
            return $this->hasOne(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        
        return $this->hasOne($agencyClass, 'app_owner_id');
    }

    /**
     * Safe shippingAgency relation
     */
    public function shippingAgency()
    {
        $shippingClass = $this->getShippingAgencyModelClass();
        
        if (!$shippingClass) {
            return $this->hasOne(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        
        return $this->hasOne($shippingClass, 'app_owner_id');
    }

    /**
     * Safe managedAgencies relation
     */
    public function managedAgencies()
    {
        $agencyClass = $this->getAgencyModelClass();
        
        if (!$agencyClass) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        
        return $this->hasMany($agencyClass, 'agency_dash_manger_id');
    }

    /**
     * Safe userAgencyJoined relation
     */
    public function userAgencyJoined()
    {
        $joinedClass = $this->getUsersJoinedAgencyModelClass();
        
        if (!$joinedClass) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        
        return $this->hasMany($joinedClass, 'user_id');
    }

    /**
     * Safe agencyJoinRequest relation
     */
    public function agencyJoinRequest()
    {
        $requestClass = $this->getAgencyJoinRequestModelClass();
        
        if (!$requestClass) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        
        return $this->hasMany($requestClass, 'user_id');
    }

    /**
     * Safe latestJoin relation
     */
    public function latestJoin()
    {
        $joinedClass = $this->getUsersJoinedAgencyModelClass();
        
        if (!$joinedClass) {
            return $this->hasOne(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        
        return $this->hasOne($joinedClass, 'user_id')
            ->where('agency_id', $this->agency_id)
            ->latestOfMany('join_date');
    }

    /**
     * Check if user has shipping agency - safe version
     */
    public function hasShippingAgency(): bool
    {
        if (!self::isShippingAgencyPackageInstalled()) {
            return false;
        }
        
        return $this->shippingAgency()->exists();
    }

    /**
     * Check if user has host agency - safe version
     */
    public function hasHostAgency()
    {
        if (!self::isAgencyPackageInstalled() && !class_exists(\App\Models\Agency::class)) {
            return $this->hasOne(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        
        $agencyClass = $this->getAgencyModelClass();
        return $this->hasOne($agencyClass, 'app_owner_id');
    }

    /**
     * Safe is_agent attribute
     */
    public function getIsAgentAttribute(): bool
    {
        if (!self::isAgencyPackageInstalled() && !class_exists(\App\Models\Agency::class)) {
            return false;
        }
        
        return $this->ownAgency()->exists();
    }

    /**
     * Safe check for shipping agency existence (used in getApplicableTypes)
     */
    protected function checkShippingAgencyOwnership(): bool
    {
        if (!self::isShippingAgencyPackageInstalled()) {
            return false;
        }
        
        $shippingClass = $this->getShippingAgencyModelClass();
        return $shippingClass::where('app_owner_id', $this->id)->exists();
    }

    /**
     * Get user types with safe agency check
     */
    protected function getAgencySafeUserTypes(): array
    {
        $userTypes = [];

        if ($this->type_user >= 1) {
            $userTypes[] = 1;
        }

        if ($this->type_user >= 2) {
            $userTypes[] = 2;
        }

        // Safe shipping agency check
        if (self::isShippingAgencyPackageInstalled()) {
            if ($this->relationLoaded('shippingAgency')) {
                if ($this->shippingAgency) {
                    $userTypes[] = 3;
                }
            } else {
                $this->loadMissing('shippingAgency');
                if ($this->shippingAgency) {
                    $userTypes[] = 3;
                }
            }
        }

        if ($this->is_bd) {
            $userTypes[] = 4;
        }

        return $userTypes;
    }
}
