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
        $agencyClass = $this->getModelClass('agency', Agency::class);
        return $this->belongsTo($agencyClass, 'agency_id');
    }

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        $userClass = $this->getModelClass('user', \App\Models\User::class);
        return $this->belongsTo($userClass, 'user_id');
    }

    /**
     * Scope for getting request managers
     */
    public function scopeRequestManagers($query)
    {
        return $query->where('type', self::TYPE_REQUEST_MANAGER);
    }

    /**
     * Scope for getting admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('type', self::TYPE_ADMIN);
    }

    /**
     * Scope for getting owners
     */
    public function scopeOwners($query)
    {
        return $query->where('type', self::TYPE_OWNER);
    }

    /**
     * Scope for getting operators
     */
    public function scopeOperators($query)
    {
        return $query->where('type', self::TYPE_OPERATOR);
    }

    /**
     * Check if is request manager
     */
    public function isRequestManager(): bool
    {
        return $this->type === self::TYPE_REQUEST_MANAGER;
    }

    /**
     * Check if is admin
     */
    public function isAdmin(): bool
    {
        return $this->type === self::TYPE_ADMIN;
    }

    /**
     * Check if is owner
     */
    public function isOwner(): bool
    {
        return $this->type === self::TYPE_OWNER;
    }

    /**
     * Check if is operator
     */
    public function isOperator(): bool
    {
        return $this->type === self::TYPE_OPERATOR;
    }
 

    /**
     * Scope by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

}
