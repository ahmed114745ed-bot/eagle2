<?php

namespace Modules\Tasks\Entities;

use App\Models\OVip;
use App\Models\Setting;
use App\Models\Ware;
use Cache;
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
        // Cache key for the timezone setting
    $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = Cache::rememberForever($cacheKey, function () {
        $setting = Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        // Cache key for the timezone setting
    $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = Cache::rememberForever($cacheKey, function () {
        $setting = Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
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
