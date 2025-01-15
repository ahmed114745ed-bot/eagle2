<?php

namespace Modules\Tasks\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Request;

class DailyTask extends Model
{
    protected $fillable = ['day_id','title_ar','type','sub_type','count','total_points','created_at', 'title_en'];
    use HasFactory;
    public function getTitleAttribute()
    {
        $locale = Request::header('X-Localization','en');
        return $locale === 'ar' ? $this->title_ar  : $this->title_en;
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
}
