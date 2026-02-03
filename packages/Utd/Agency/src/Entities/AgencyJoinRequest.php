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
        return $this->belongsTo($this->getModelClass('user'), 'user_id');
    }

    /**
     * Relationship with Admin (who changed status)
     */
    public function admin(): BelongsTo
    {
        $adminModel = config('agency-package.models.admin', \App\Models\Agent::class);
        return $this->belongsTo($adminModel, 'change_status_admin_id');
    }

    /**
     * Relationship with User Operator (who changed status from app)
     */
    public function userOperator(): BelongsTo
    {
        return $this->belongsTo($this->getModelClass('user'), 'change_status_admin_id');
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
}
