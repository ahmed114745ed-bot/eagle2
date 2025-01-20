<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Modules\FixedTarget\Enums\TargetType;

class UserSallary extends Model
{
    protected $table = 'user_sallaries';

public bool $allowSaving = true;

    protected $guarded = ['id'];

    protected $casts = [
        'agency_sallary' => 'double',
        'sallary' => 'double',
        'extras' => 'json',
        'type' => TargetType::class,
        'year' => 'integer',
        'month' => 'integer',
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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function period_target(){
        return $this->belongsTo(PeriodTarget::class,'month','id');
    }
}
