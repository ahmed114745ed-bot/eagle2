<?php

namespace Modules\Achievement\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Achievement\Enums\TargetType;

class AchievementLevel extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $fillable = [];

    protected $casts = [
        'target_type' => TargetType::class
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
    public function achievement()
    {
        return $this->hasOne(Achievement::class, 'id', 'achievement_id');
    }

    public function achievements()
    {
        return $this->belongsTo(Achievement::class, 'achievement_id');
    }

    public function achievementUsers()
    {
        return $this->hasMany(UserAchievementLevel::class, 'achievement_level_id', 'id');
    }
}
