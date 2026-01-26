<?php

/*
|--------------------------------------------------------------------------
| Preview Routes (Admin Panel)
|--------------------------------------------------------------------------
|
| These routes handle the admin panel preview functionality for achievements.
|
*/

use Illuminate\Support\Facades\Route;
use Utd\Achievements\Http\Controllers\web\AchievementsController;
use Utd\Achievements\Http\Controllers\web\AchievementsLevelsController;
use Utd\Achievements\Http\Controllers\web\AchievementLevelsModuleController;
use Utd\Achievements\Http\Controllers\web\UserAchievementLevelController;
use Utd\Achievements\Http\Controllers\web\GiftAchievemntController;
use Utd\Achievements\Http\Controllers\web\GiftAchiementController;
use Utd\Achievements\Http\Controllers\web\UserGiftAchController;

Route::group(
    [
        'prefix'     => 'preview/' . config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
            'prevent-delete',
            'appFeatureEnable:achievement',
        ],
        'as' => 'preview.' . config('admin.route.prefix') . '.',
    ],
    function () {
        Route::resource('achievements', AchievementsController::class);
        Route::post('/store-user-achievement', [AchievementLevelsModuleController::class, 'store'])->name('store-user-achievement');
        Route::get('/get-achievement-levels/{achievementId}', [AchievementLevelsModuleController::class, 'getAchievementLevels'])->name('get-achievement-levels');
        Route::get('/get-view-page', [AchievementLevelsModuleController::class, 'viewPage'])->name('get-view-page');
        Route::resource('user-achievement-levels', UserAchievementLevelController::class);
        Route::get('achievement-levels/create/{id}', [AchievementsLevelsController::class, 'create'])->where('id', '[0-9]+')->name('achievement-levels.create');
        Route::resource('gift-achievements', UserGiftAchController::class);
        Route::resource('gift-achievment', GiftAchiementController::class);
        Route::post('postAddGiftAchievement', [GiftAchievemntController::class, 'postAddGiftAchievemnt'])->name('postAddGiftAchievement');
        Route::post('postAddGiftAchievementLevel', [GiftAchievemntController::class, 'postAddGiftAchievementLevel'])->name('postAddGiftAchievementLevel');
        Route::post('posteditGiftAchievementLevel', [GiftAchievemntController::class, 'posteditGiftAchievementLevel'])->name('posteditGiftAchievementLevel');
        Route::resource('achievement-levels', AchievementsLevelsController::class, ['names' => ['create' => 'achievement-levels.create2']]);
    }
);
