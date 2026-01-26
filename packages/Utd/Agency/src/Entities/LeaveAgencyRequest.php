<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAgencyRequest extends Model
{
    protected $table = 'leave_agency_requests';

    protected $guarded = [];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status Constants
     */
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
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
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Scope for pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Check if is pending
     */
    public function isPending(): bool
    {
        return $this->status == self::STATUS_PENDING;
    }

    /**
     * Approve the request
     */
    public function approve(): bool
    {
        $this->status = self::STATUS_APPROVED;
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
