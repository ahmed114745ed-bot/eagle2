<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Utd\Agency\Traits\ConfigurableModelsTrait;

class HostAgencyInvite extends Model
{
    use ConfigurableModelsTrait;

    protected $table = 'host_agency_invites';

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
     * Relationship with User (invited user)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo($this->getModelClass('user'), 'user_id');
    }

    /**
     * Relationship with Inviter
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo($this->getModelClass('user'), 'inviter_id');
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
     * Accept the invitation
     */
    public function accept(): bool
    {
        $this->status = self::STATUS_ACCEPTED;
        return $this->save();
    }

    /**
     * Reject the invitation
     */
    public function reject(): bool
    {
        $this->status = self::STATUS_REJECTED;
        return $this->save();
    }
}
