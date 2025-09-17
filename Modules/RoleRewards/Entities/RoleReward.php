<?php

namespace Modules\RoleRewards\Entities;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;

class RoleReward extends Model
{
    protected $fillable = [
        'role_id',
        'rewardable_id',
        'rewardable_type',
        'type',
        'expire',
        'reward_achievement'
    ];

    /**
     * العلاقة مع الدور
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * العلاقة المورفية
     */
    public function rewardable()
    {
        return $this->morphTo();
    }
}
