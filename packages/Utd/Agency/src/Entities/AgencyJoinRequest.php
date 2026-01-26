<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencyJoinRequest extends Model
{
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
        return $this->belongsTo(\App\Models\User::class, 'user_id');
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
