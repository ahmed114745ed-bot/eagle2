<?php

namespace Modules\FixedTarget\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FixedTarget extends Model
{
    protected $fillable = [
        'id',
        'diamonds',
        'hours',
        'days',
        'count_moment',
        'count_real',
        'usd',
        'agency_share',
        'img',
        'coin',
    ];

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
