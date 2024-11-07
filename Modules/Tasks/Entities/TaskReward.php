<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Events\Entities\Reward;

class TaskReward extends Model
{
    /*protected $fillable = ['day_id', 'type', 'target', 'expire'];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($taskReward) {
            $reward = Reward::find($taskReward->type);
            $taskReward->target = $reward ? $reward->target : null;
        });
    }*/
    use HasFactory;
}
