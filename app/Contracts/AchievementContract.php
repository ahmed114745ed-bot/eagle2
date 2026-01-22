<?php

namespace App\Contracts;

use App\Models\User;

interface AchievementContract
{
    public function show(User $user, int $page = 1);
    
    public function all();
    
    public function allAchievementLevel($achievementId, $perPage, $Page);
    
    public function createAchievementLevel($request);
    
    public function updateAchievementLevel($id, $request);
    
    public function deleteAchievementLevel($id);
    
    public function showAchievementLevel($id);
    
    public function achievementTargetType();
    
    public function allAchievementGift($achievementId, $perPage, $Page);
    
    public function achievementGift($request);
    
    public function giftAchievement();
    
    public function userAchievementLevel($perPage, $Page, $uuid);
    
    public function isEnable($id, $isEnable);
    
    public function deleteUserAchievementLevel($id);
    
    public function giftAchievementIndex($perPage, $Page);
    
    public function getAchievementLevelsTarget($achievementId);
    
    public function createUserAchievementLevel($request);
}
