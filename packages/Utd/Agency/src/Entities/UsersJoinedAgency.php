<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Utd\Agency\Traits\ConfigurableModelsTrait;
use Utd\Agency\Traits\TimestampsWithTimezone;

class UsersJoinedAgency extends Model
{
    use ConfigurableModelsTrait, TimestampsWithTimezone;

    protected $table = 'users_joined_agencies';

    protected $guarded = [];

    protected $casts = [
        'join_date' => 'datetime',
        'leave_date' => 'datetime',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Agency
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo($this->getModelClass('user'), 'user_id');
    }

    /**
     * Relationship with kicked by admin
     */
    public function kickedByAdmin(): BelongsTo
    {
        return $this->belongsTo($this->getModelClass('admin'), 'kicked_by_admin');
    }

    /**
     * Relationship with kicked by app user
     */
    public function kickedByApp(): BelongsTo
    {
        return $this->belongsTo($this->getModelClass('user'), 'kicked_by_app');
    }

    /**
     * Scope for active memberships
     */
    public function scopeActive($query)
    {
        return $query->whereNull('leave_date');
    }

    /**
     * Scope for past memberships
     */
    public function scopePast($query)
    {
        return $query->whereNotNull('leave_date');
    }

    /**
     * Check if membership is active
     */
    public function isActive(): bool
    {
        return is_null($this->leave_date);
    }

    /**
     * End membership
     */
    public function endMembership(): bool
    {
        $this->leave_date = now();
        return $this->save();
    }
}
