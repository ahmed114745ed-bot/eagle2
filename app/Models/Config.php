<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $guarded = ['id'];

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
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->valueSelect) {
                unset($model->valueSelect);
            }
            if ($model->valueInteger) {
                unset($model->valueInteger);
            }
            $Keys = ['app_id', 'app_key', 'app_secret', 'app_cluster'];
            foreach ($Keys as $key) {
                if ($model->isDirty('value') && $model->name == $key) {
                    if ($model->name == $key) {
                        Cache::forget('pusher_config');
                        \Artisan::call('config:cache');
                        break;
                    }
                }
            }
        });
    }
}
