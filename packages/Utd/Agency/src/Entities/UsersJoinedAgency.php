<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Utd\Agency\Traits\ConfigurableModelsTrait;

class UsersJoinedAgency extends Model
{
    use ConfigurableModelsTrait;

    protected $table = 'users_joined_agency';

    protected $guarded = [];

    protected $casts = [
        'join_date' => 'datetime',
        'leave_date' => 'datetime',
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
