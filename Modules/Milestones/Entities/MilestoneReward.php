<?php

namespace Modules\Milestones\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MilestoneReward extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'milestone_id',
        'rewardable_id',
        'rewardable_type',
        'reward',
        'type',
        'expire',
    ];

    protected $dates = ['deleted_at'];

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function rewardable(): MorphTo
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
            elseif ($model->type === 'achievement') {
                $model->reward =  $model->reward1;
            }
            elseif ($model->type === 'coins') {
                $model->reward = request('reward2', $model->reward);
            }
            unset($model->rewardable_id2);
            unset($model->rewardable_id3);
            unset($model->reward1);
            unset($model->reward2);
        
        });

        self::updating(function ($model) {
            if ($model->type === 'ware') {
                $model->rewardable_id = request('rewardable_id', $model->rewardable_id);
            } elseif ($model->type === 'vip') {
                $model->rewardable_id = request('rewardable_id2', $model->rewardable_id);
            }elseif ($model->type === 'badge') {
                $model->rewardable_id = request('rewardable_id3', $model->rewardable_id);
            } 
            elseif ($model->type === 'achievement') {
                $model->reward =  $model->reward1;
            } 
            elseif ($model->type === 'coins') {
                $model->reward = request('reward2', $model->reward2);
            }
            unset($model->rewardable_id2);
            unset($model->rewardable_id3);
            unset($model->reward1);
            unset($model->reward2);
        
        });
    }
}
