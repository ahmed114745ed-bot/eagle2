<?php

namespace Modules\Achievement\Http\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Achievement\Entities\UserAchievementLevel;

trait AchievementUser
{
    public function medals(): HasMany
    {
        return $this->hasMany(UserAchievementLevel::class, 'user_id');
    }

}

