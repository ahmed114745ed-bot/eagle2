<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdditionalInfo extends Model
{
    /**
     * Status Constants
     */
    public const STATUS_PENDING = 0;

    public const STATUS_APPROVED = 1;

    public const STATUS_REJECTED = 2;

    protected $table = 'additional_infos';

    protected $guarded = [];

    protected $casts = [
        'status' => 'integer',
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
     * Scope for pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Check if is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Approve the info
     */
    public function approve(): bool
    {
        $this->status = self::STATUS_APPROVED;

        return $this->save();
    }

    /**
     * Reject the info
     */
    public function reject(): bool
    {
        $this->status = self::STATUS_REJECTED;

        return $this->save();
    }
}
