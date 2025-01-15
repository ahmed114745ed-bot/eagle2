<?php

namespace Modules\Tasks\Entities;

use App\Models\OVip;
use App\Models\Ware;
use Carbon\Carbon;
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

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if ($model->coins) {
                unset($model->coins);
            }
            if ($model->achievement) {
                unset($model->achievement);
            }
        });

    }

    public function getCreatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }
    public function vip()
    {
        return $this->belongsTo(OVip::class,'target');
    }
    public function ware()
    {
        return $this->belongsTo(Ware::class,'target');
    }

}
