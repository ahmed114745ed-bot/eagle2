<?php

namespace Modules\Achievement\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Achievement\Enums\AchievementType;
use Modules\Achievement\Enums\TargetType;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $guarded = [];

    protected $casts = [
      'type' => AchievementType::class,
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
    public function levels()
    {
        return $this->hasMany(AchievementLevel::class, 'achievement_id', 'id' );
    }

    public function userAchievement()
    {
        return $this->hasManyThrough(User::class, UserAchievement::class,'user_id','id');
    }

    public function userAchievments()
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function userAchievementLevel()
    {
        return $this->hasMany(UserAchievementLevel::class);
    }
}
