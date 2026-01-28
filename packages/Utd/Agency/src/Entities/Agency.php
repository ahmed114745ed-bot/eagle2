<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Utd\Agency\Scopes\HostAgencyScope;
use Utd\Agency\Traits\AgencyRelationsTrait;
use Utd\Agency\Traits\AgencySalaryTrait;

class Agency extends Model
{
    use SoftDeletes, AgencyRelationsTrait, AgencySalaryTrait;

    protected $table = 'agencies';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'salary',
    ];

    protected $casts = [
        'status' => 'integer',
        'type' => 'integer',
        'is_frozen' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        self::addGlobalScope(new HostAgencyScope);

        self::saving(function ($model) {
            $model->type = 1;

            if (request()->has('phone_code')) {
                $model->phone_code = request('phone_code');
            }
        });

        self::updating(function ($agency) {
            if (function_exists('clearAgencyCache')) {
                clearAgencyCache($agency->id);
            }

            if (isset($agency->is_frozen)) {
                $agency->is_frozen = (bool) $agency->is_frozen;
            }
        });

        self::deleting(function ($agency) {
            if (function_exists('clearAgencyCache')) {
                clearAgencyCache($agency->id);
            }
        });
    }

    /**
     * Hash password on setting
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /**
     * Get URL attribute
     */
    public function getUrlAttribute($val)
    {
        return $val ?: '';
    }

    /**
     * Get contents attribute
     */
    public function getContentsAttribute($val)
    {
        return $val ?: '';
    }

    /**
     * Get is_frozen attribute
     */
    public function getIsFrozenAttribute($value)
    {
        return $value ?? 0;
    }

    /**
     * Scope for active agencies
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for pending agencies
     */
    public function scopePending($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope by owner
     */
    public function scopeOfOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    /**
     * Scope for available agencies
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereDoesntHave('additionalInfo')
                ->orWhereHas('additionalInfo', fn($q) => $q->where('status', 1));
        })->whereNull('deleted_at')->where('type', 1);
    }

    /**
     * Check if agency is active
     */
    public function isActive(): bool
    {
        return $this->status == 1;
    }

    /**
     * Check if agency is frozen
     */
    public function isFrozen(): bool
    {
        return (bool) $this->is_frozen;
    }

    /**
     * Get owner user id
     */
    public function ownerUserId(): ?int
    {
        return $this->app_owner_id ?? null;
    }

    /**
     * Get BD user id
     */
    public function bdUserId(): ?int
    {
        return $this->bd_id ?? null;
    }
}
