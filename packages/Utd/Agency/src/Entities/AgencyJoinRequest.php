<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Utd\Agency\Traits\ConfigurableModelsTrait;

class AgencyJoinRequest extends Model
{
    use ConfigurableModelsTrait;

    protected $table = 'agency_join_requests';

    protected $guarded = [];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_REJECTED = 2;

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
        $userClass = config('agency-package.models.user', \App\Models\User::class);
        return $this->belongsTo($userClass, 'user_id');
    }

    /**
     * Relationship with Admin (who changed status)
     */
    public function admin(): BelongsTo
    {
        $agentClass = config('agency-package.models.agent', \App\Models\Agent::class);
        return $this->belongsTo($agentClass, 'change_status_admin_id');
    }

    /**
     * Relationship with User Operator (who changed status from app)
     */
    public function userOperator(): BelongsTo
    {
        $userClass = config('agency-package.models.user', \App\Models\User::class);
        return $this->belongsTo($userClass, 'change_status_admin_id');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for accepted requests
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status == self::STATUS_PENDING;
    }

    /**
     * Accept the request
     */
    public function accept(): bool
    {
        $this->status = self::STATUS_ACCEPTED;
        return $this->save();
    }

    /**
     * Reject the request
     */
    public function reject(): bool
    {
        $this->status = self::STATUS_REJECTED;
        return $this->save();
    }

    /**
     * Alias for user relationship
     */
    public function requsers()
    {
        $userClass = config('agency-package.models.user', \App\Models\User::class);
        return $this->belongsTo($userClass, 'user_id', 'id');
    }

    /**
     * Override update to handle type_user
     */
    public function update(array $attributes = [], array $options = [])
    {
        if ($this->agency_id === 0) {
            $attributes['type_user'] = 0;
        }

        return parent::update($attributes, $options);
    }

    /**
     * Boot model events
     */
    protected static function booted()
    {
        self::saved(function ($model) {
            if ($model->agency_id && function_exists('clearAgencyCache')) {
                clearAgencyCache($model->agency_id);
            }
        });

        self::deleted(function ($model) {
            if ($model->agency_id && function_exists('clearAgencyCache')) {
                clearAgencyCache($model->agency_id);
            }
        });
    }
}
