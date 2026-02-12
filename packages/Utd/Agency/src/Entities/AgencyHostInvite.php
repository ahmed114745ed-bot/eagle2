<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Utd\Agency\Traits\ConfigurableModelsTrait;

class AgencyHostInvite extends Model
{
    use ConfigurableModelsTrait;

    /**
     * Status constants
     */
    public const STATUS_PENDING = 0;

    public const STATUS_ACCEPTED = 1;

    public const STATUS_REJECTED = 2;

    public const STATUS_CANCELLED = 3;

    protected $table = 'agency_host_invites';

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
