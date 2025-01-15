<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuckyGift extends Model
{
    use HasFactory;
    protected $fillable = ['gift_id', 'win_probability'];

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
