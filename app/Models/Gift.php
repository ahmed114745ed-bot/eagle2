<?php

namespace App\Models;

use Carbon\Carbon;
use Modules\Moment\Entities\Moment;
use Illuminate\Database\Eloquent\Model;
use Modules\Achievement\Http\Traits\AchievementGift;

class Gift extends Model
{
    use AchievementGift;
   // protected $fillable=['use_count'];
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
    public function luckyGift()
    {
        return $this->hasOne(LuckyGift::class);
    }

    public function moments()
    {
        return $this->belongsToMany(Moment::class, 'moment_user_gifts')->withPivot('num', 'created_at','updated_at')->withTimestamps();
    }

    public function lucky_gift ()
    {
        return $this->hasOne(LuckyGift::class,'gift_id');
    }
}
