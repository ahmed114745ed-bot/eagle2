<?php

namespace App\Services\Null;

use App\Contracts\AchievementContract;
use App\Models\User;

class NullAchievementService implements AchievementContract
{
    public function show(User $user, int $page = 1)
    {
        return collect();
    }

    public function all()
    {
        return collect();
    }

    public function allAchievementLevel($achievementId, $perPage, $Page)
    {
        return collect();
    }

    public function createAchievementLevel($request)
    {
        return false;
    }

    public function updateAchievementLevel($id, $request)
    {
        return false;
    }

    public function deleteAchievementLevel($id)
    {
        return false;
    }

    public function showAchievementLevel($id)
    {
        return null;
    }

    public function achievementTargetType()
    {
        return [];
    }

    public function allAchievementGift($achievementId, $perPage, $Page)
    {
        return collect();
    }

    public function achievementGift($request)
    {
        return false;
    }

    public function giftAchievement()
    {
        return collect();
    }

    public function userAchievementLevel($perPage, $Page, $uuid)
    {
        return collect();
    }

    public function isEnable($id, $isEnable)
    {
        return false;
    }

    public function deleteUserAchievementLevel($id)
    {
        return false;
    }

    public function giftAchievementIndex($perPage, $Page)
    {
        return collect();
    }

    public function getAchievementLevelsTarget($achievementId)
    {
        return collect();
    }

    public function createUserAchievementLevel($request)
    {
        return false;
    }
}
