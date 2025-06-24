<?php

namespace Modules\Tasks\Entities;

use App\Models\OVip;
use App\Models\Ware;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    use HasFactory, TimestampsWithTimezone;

    public function vip()
    {
        return $this->belongsTo(OVip::class, 'target');
    }

    public function ware()
    {
        return $this->belongsTo(Ware::class, 'target');
    }

    protected static function boot()
    {
        parent::boot();
        self::saving(function ($model) {
            if ($model->coins) {
                unset($model->coins);
            }
            if ($model->achievement) {
                unset($model->achievement);
            }
        });
    }
}
