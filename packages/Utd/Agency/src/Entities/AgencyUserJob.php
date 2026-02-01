<?php

namespace Utd\Agency\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Utd\Agency\Traits\ConfigurableModelsTrait;

class AgencyUserJob extends Model
{
    use ConfigurableModelsTrait;

    protected $table = 'agency_user_jobs';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Job Type Constants
     */
    const TYPE_OWNER = 'owner';
    const TYPE_ADMIN = 'admin';
    const TYPE_REQUEST_MANAGER = 'requestManger';
    const TYPE_OPERATOR = 'operator';

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
     * Scope by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('type', self::TYPE_REQUEST_MANAGER);
    }

    /**
     * Scope for operators
     */
    public function scopeOperators($query)
    {
        return $query->where('type', self::TYPE_OPERATOR);
    }

    /**
     * Check if is admin
     */
    public function isAdmin(): bool
    {
        return $this->type == self::TYPE_REQUEST_MANAGER;
    }

    /**
     * Check if is operator
     */
    public function isOperator(): bool
    {
        return $this->type == self::TYPE_OPERATOR;
    }
}
