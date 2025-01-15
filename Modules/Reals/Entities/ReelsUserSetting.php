<?php

namespace Modules\Reals\Entities;

use App\Models\Interest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ReelsUserSetting extends Model
{
    protected $fillable = [];

    protected $guarded = ['id'];

    protected $table = 'reels_user_settings';

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
