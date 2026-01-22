<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Utd\Achievements\Entities\UserAchievementLevel;

trait DynamicAchievementTrait
{
    public function medals(): HasMany
    {
        if (!class_exists(UserAchievementLevel::class)) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->hasMany(UserAchievementLevel::class, 'user_id');
    }

    public function enabledMedals(): HasMany
    {
        if (!class_exists(UserAchievementLevel::class)) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->hasMany(UserAchievementLevel::class, 'user_id')->where('is_enable', true);
    }
}
