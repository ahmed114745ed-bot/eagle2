<?php

namespace Modules\Milestones\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MilestoneReward extends Model
{
    protected $fillable = [
        'milestone_id',
        'rewardable_id',
        'rewardable_type',
        'reward',
        'type',
        'expire',
    ];

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function rewardable(): MorphTo
    {
        return $this->morphTo();
    }
}
