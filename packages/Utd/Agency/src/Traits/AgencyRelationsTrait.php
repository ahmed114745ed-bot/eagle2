<?php

namespace Utd\Agency\Traits;

use Utd\Agency\Entities\AdditionalInfo;
use Utd\Agency\Entities\AgencyJoinRequest;
use Utd\Agency\Entities\AgencySalary;
use Utd\Agency\Entities\AgencyUserJob;
use Utd\Agency\Entities\UsersJoinedAgency;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait AgencyRelationsTrait
{
    /**
     * Relationship with members (users)
     */
    public function members(): HasMany
    {
        return $this->hasMany(\App\Models\User::class, 'agency_id');
    }

    /**
     * Alias for members
     */
    public function mempers(): HasMany
    {
        return $this->members();
    }

    /**
     * Relationship with users
     */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class, 'agency_id');
    }

    /**
     * Relationship with admins
     */
    public function admins(): HasMany
    {
        return $this->hasMany(AgencyUserJob::class, 'agency_id')
            ->where('type', 'requestManger');
    }

    /**
     * Relationship with owner (app owner)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'app_owner_id', 'id');
    }

    /**
     * Relationship with agency manager
     */
    public function agencyManger(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'agency_manger_id', 'id');
    }

    /**
     * Relationship with dashboard owner
     */
    public function dashOwner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin::class, 'owner_id', 'id');
    }

    /**
     * Relationship with BD
     */
    public function bd(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Bd::class, 'bd_id');
    }

    /**
     * Relationship with creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AdminUser::class, 'created_by');
    }

    /**
     * Relationship with country
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Country::class);
    }

    /**
     * Relationship with countries (many to many)
     */
    public function Countries(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Country::class,
            'agency_countries',
            'agency_id',
            'country_id'
        )->withTimestamps();
    }

    /**
     * Relationship with join requests
     */
    public function joinRequests(): HasMany
    {
        return $this->hasMany(AgencyJoinRequest::class, 'agency_id');
    }

    /**
     * Relationship with agency salary (current month)
     */
    public function agencySalary(): HasOne
    {
        return $this->hasOne(AgencySalary::class, 'agency_id')
            ->orderByDesc('id')
            ->where('month', now()->month)
            ->where('year', now()->year);
    }

    /**
     * Relationship with all agency salaries
     */
    public function agencySalaries(): HasMany
    {
        return $this->hasMany(AgencySalary::class, 'agency_id')
            ->orderByDesc('id');
    }

    /**
     * Relationship with user salaries
     */
    public function userSalaries(): HasMany
    {
        return $this->hasMany(\App\Models\UserSallary::class, 'user_agency_id');
    }

    /**
     * Relationship with additional info
     */
    public function additionalInfo(): HasOne
    {
        return $this->hasOne(AdditionalInfo::class, 'agency_id');
    }

    /**
     * Relationship with users joined agency history
     */
    public function usersJoinedHistory(): HasMany
    {
        return $this->hasMany(UsersJoinedAgency::class, 'agency_id');
    }

    /**
     * Relationship with agency user jobs
     */
    public function agencyUserJobs(): HasMany
    {
        return $this->hasMany(AgencyUserJob::class, 'agency_id');
    }

    /**
     * Relationship with charges
     */
    public function charges(): HasMany
    {
        return $this->hasMany(\App\Models\Charge::class, 'user_id', 'id')
            ->where('charger_type', 'host_agency');
    }

    /**
     * Relationship with sender charges
     */
    public function senderCharges(): HasMany
    {
        return $this->hasMany(\App\Models\Charge::class, 'charger_id', 'id')
            ->where('charger_type', 'host_agency');
    }

    /**
     * Relationship with users targets
     */
    public function AgencyUsersTargets(): HasMany
    {
        return $this->hasMany(\App\Models\UserTarget::class, 'agency_id');
    }
}
