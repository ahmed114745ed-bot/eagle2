<?php

namespace Utd\Achievements\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Utd\Achievements\Enums\TargetType;

class AchievementLevel extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'target_type' => TargetType::class,
    ];

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
