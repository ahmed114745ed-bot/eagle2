<?php

namespace Modules\Public\Entities;

use App\Models\OVip;
use App\Models\Ware;
use App\Helpers\Common;
use App\Models\Setting;
use Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RewardLevelInterval extends Model
{
    protected $guarded = ['id'];
    protected $table = 'reward_level_intervals';
    protected $appends = ['target1', 'target2', 'target3','target4'];

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
    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if ($model->type == "ware"){
                $model->target = request('target1', $model->target);
            }elseif ($model->type == "vip"){
                $model->target = request('target2', $model->target);
            }elseif ($model->type == "coins"){
                $model->target = request('target3', $model->target);
            }elseif ($model->type == "achievement"){
                $file       = request('target4', $model->target);

                if ($file instanceof  UploadedFile){
                    $url = Common::upload('events', $file);
                }
                $model->target = $url ?? '';
            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);
        });

        static::updating(function ($model) {
            if ($model->type == "ware"){
                $model->target = request('target1', $model->target);
            }elseif ($model->type == "vip"){
                $model->target = request('target2', $model->target);
            }elseif ($model->type == "coins"){
                $model->target = request('target3', $model->target);
            }elseif ($model->type == "achievement"){
                $file       = request('target4', $model->target);
                if ($file instanceof  UploadedFile){
                    $url = Common::upload('events', $file);
                    Storage::delete($model->target);
                }
                $model->target = $url ?? '';
//                $model->target = request('target4', $model->target);

            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);
        });
    }

    public function levelInterval()
    {
           return $this->belongsTo(LevelInterval::class,'level_interval_id');
    }

    public function ware()
    {
        return $this->hasOne(Ware::class,'id','target');
    }

    public function vip()
    {
        return $this->hasOne(OVip::class,'id','target');
    }

    public function getTarget1Attribute()
    {
        return $this->target;
    }
    public function getTarget2Attribute()
    {
        return $this->target;
    }
    public function getTarget3Attribute()
    {
        return $this->target;
    }
    public function getTarget4Attribute()
    {
        return $this->target;
    }
}
