<?php

use Illuminate\Support\Facades\Route;
use Utd\Achievements\Http\Controllers\UtdAchievementController;

Route::group([
    'middleware' => ['api', 'localization'],
    'prefix' => 'api/utd',
], function () {
    Route::prefix('achievements')->group(function () {
        Route::get('/all', [UtdAchievementController::class, 'allAchievements']);
        Route::get('/{achievementId}/level', [UtdAchievementController::class, 'allAchievementsLevel']);
        Route::post('/create-level', [UtdAchievementController::class, 'createAchievementLevel']);
        Route::post('/update-level/{id}', [UtdAchievementController::class, 'updateAchievementLevel']);
        Route::get('/show/{id}', [UtdAchievementController::class, 'showAchievementLevel']);
        Route::get('/target-level', [UtdAchievementController::class, 'achievementTargetType']);
        Route::get('/{achievementId}/all-users-gift-achievement', [UtdAchievementController::class, 'allUsersGiftAchievements']);
        Route::post('/create-user-gift', [UtdAchievementController::class, 'createUserAchievementGift']);
        Route::get('/gift-achievement', [UtdAchievementController::class, 'giftAchievement']);
        Route::get('/user-achievement-level', [UtdAchievementController::class, 'allUserAchievementLevel']);
        Route::post('/enable/{id}', [UtdAchievementController::class, 'isEnable']);
        Route::post('/delete_user_level/{id}', [UtdAchievementController::class, 'deleteUserAchievementLevel']);
        Route::post('/create/user-level', [UtdAchievementController::class, 'createUserAchievementLevel']);
        Route::get('/gift-user-level', [UtdAchievementController::class, 'userAchievementLevelGiftIndex']);
        Route::get('/target-user-level/{achievementId}', [UtdAchievementController::class, 'getAchievementLevelsTarget']);
    });
});
