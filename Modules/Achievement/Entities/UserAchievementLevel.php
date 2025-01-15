<?php

namespace Modules\Achievement\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Modules\Achievement\Entities\Achievement;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Achievement\Enums\AchievementType;
use Modules\Achievement\Http\Services\AchievementLevelsService;

class UserAchievementLevel extends Model
{
    use HasFactory;

    protected $fillable = ["id","achievement_level_id","user_id","gift_achievement_id","unique_value","end_at","is_enable","achievement_id","custom_image","picked"];

    protected $guarded = [];


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
    public function achievementLevel(): BelongsTo
    {
        return $this->belongsTo(AchievementLevel::class, 'achievement_level_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class, 'achievement_id');
    }

    public function giftAchievement(): BelongsTo
    {
        return $this->belongsTo(GiftAchievement::class, 'gift_achievement_id');
    }

    // public function achievement()
    // {
    //     return $this->belongsTo(Achievement::class, 'achievement_id');
    // }

    public function scopeWithAllData(Builder $query): Builder
    {
        return $query->with([
            'achievementLevel' => function ($query) {
                $query->select(['id', 'valid_image', 'target']);
            },
            'Achievement'
        ]);
    }


    public function scopeUserPickProfile(Builder $builder): Builder
    {
        return $builder->where("picked",1)->whereDoesntHave('achievement', fn($q) => $q->where('type' , AchievementType::ROOM_TARGET->getValue()));
    }
}
