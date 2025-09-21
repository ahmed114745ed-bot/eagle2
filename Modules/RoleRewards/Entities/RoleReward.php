<?php

namespace Modules\RoleRewards\Entities;

use App\Helpers\Common;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

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


    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if ($model->type === 'ware') {
                $model->rewardable_id = request('rewardable_id', $model->rewardable_id);
            } elseif ($model->type === 'vip') {
                $model->rewardable_id = request('rewardable_id2', $model->rewardable_id);
            } elseif ($model->type === 'badge') {
                $model->rewardable_id = request('rewardable_id3', $model->rewardable_id);
            }
            unset($model->rewardable_id2);
            unset($model->rewardable_id3);
        
        });

        self::updating(function ($model) {
            if ($model->type === 'ware') {
                $model->rewardable_id = request('rewardable_id', $model->rewardable_id);
            } elseif ($model->type === 'vip') {
                $model->rewardable_id = request('rewardable_id2', $model->rewardable_id);
            }elseif ($model->type === 'badge') {
                $model->rewardable_id = request('rewardable_id3', $model->rewardable_id);
            } 
            unset($model->rewardable_id2);
            unset($model->rewardable_id3);
        
        });
    }
}
