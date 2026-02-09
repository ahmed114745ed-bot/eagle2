<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Utd\Agency\Traits\ConfigurableModelsTrait;

class AgencyHostInvite extends Model
{
    use ConfigurableModelsTrait;

    protected $table = 'agency_host_invites';

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
    const STATUS_CANCELLED = 3;

    /**
     * Relationship with Agency
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo($this->getModelClass('user'), 'agency_id');
    }

    /**
     * Relationship with invited User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo($this->getModelClass('user'), 'user_id');
    }

    /**
     * Relationship with User who sent the invite
     */
    public function userInvite(): BelongsTo
    {
        return $this->belongsTo($this->getModelClass('user'), 'user_invite_id');
    }
}
