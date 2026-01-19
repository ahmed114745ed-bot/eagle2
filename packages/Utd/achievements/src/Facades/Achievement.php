<?php

namespace Utd\Achievements\Facades;

use Illuminate\Support\Facades\Facade;
use Utd\Achievements\Contracts\AchievementServiceInterface;

/**
 * @method static \Illuminate\Support\Collection getUserAchievements(\Illuminate\Database\Eloquent\Model $user)
 * @method static \Illuminate\Support\Collection getUserMedals(\Illuminate\Database\Eloquent\Model $user)
 * @method static void trackCharging(\Illuminate\Database\Eloquent\Model $user, int $totalCoins)
 * @method static void trackRoomTarget(\Illuminate\Database\Eloquent\Model $user, int $totalCoins)
 * @method static void trackGiftTarget(\Illuminate\Database\Eloquent\Model $gift, int $total)
 * @method static bool isEnabled()
 * @method static \Illuminate\Support\Collection all()
 *
 * @see \Utd\Achievements\Services\AchievementService
 */
class Achievement extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AchievementServiceInterface::class;
    }
}
