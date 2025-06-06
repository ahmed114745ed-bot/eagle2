<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuckyGift extends Model
{
    use HasFactory;
    protected $fillable = ['gift_id', 'win_probability'];


    protected $appends = ['min_percentag','mid_percentag','max_percentag',];
    public function getCreatedAtAttribute($value)
    {
        $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
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
        $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }


    public function getMinPercentagAttribute() : int
    {
        return intval(@explode(',', $this->min_percentage)[0] ?? 0);
    }

    public function getMidPercentagAttribute() : int
    {
        return intval(@explode(',', $this->min_percentage)[1] ?? 0);
    }

    public function getMaxPercentagAttribute() : int
    {
        return intval(@explode(',', $this->min_percentage)[2] ?? 0);
    }


    public function setMinPercentagAttribute($value): void
    {
        $parts = explode(',', $this->min_percentage ?? '0,0,0');
        $parts[0] = $value;
        $this->min_percentage = implode(',', $parts);
    }

    public function setMidPercentagAttribute($value): void
    {
        $parts = explode(',', $this->min_percentage ?? '0,0,0');
        $parts[1] = $value;
        $this->min_percentage = implode(',', $parts);
    }

    public function setMaxPercentagAttribute($value): void
    {
        $parts = explode(',', $this->min_percentage ?? '0,0,0');
        $parts[2] = $value;
        $this->min_percentage = implode(',', $parts);
    }

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if ($model->win_probability == null ) {
                return false;
            }

            $model->min_percentage =$model->min_percentag .','.$model->mid_percentag.','.$model->max_percentag;

            unset($model->min_percentag);
            unset($model->mid_percentag);
            unset($model->max_percentag);
        });
        static::updating(function ($model) {
            if ($model->win_probability == null ) {
                return false;
            }
            $model->min_percentage =$model->min_percentag .','.$model->mid_percentag.','.$model->max_percentag;

            unset($model->min_percentag);
            unset($model->mid_percentag);
            unset($model->max_percentag);
        });
    }
}
