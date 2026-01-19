<?php

/*
|--------------------------------------------------------------------------
| Achievement Web Routes (Admin Panel)
|--------------------------------------------------------------------------
|
| These routes are for the Laravel Admin panel integration.
| They will be loaded automatically when the package is installed.
|
*/

use Illuminate\Support\Facades\Route;
use Utd\Achievements\Http\Controllers\Web\AchievementsController;
use Utd\Achievements\Http\Controllers\Web\AchievementsLevelsController;
use Utd\Achievements\Http\Controllers\Web\AchievementDedicateController;
use Utd\Achievements\Http\Controllers\Web\UserAchievementLevelController;
use Utd\Achievements\Http\Controllers\Web\AchievementLevelsModuleController;
use Utd\Achievements\Http\Controllers\Web\GiftAchievementController;
use Utd\Achievements\Http\Controllers\Web\UserGiftAchievementController;
use Utd\Achievements\Http\Controllers\Web\GiftTypeController;

Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'middleware' => array_merge(
            config('admin.route.middleware', ['web', 'admin']),
            ['appFeatureEnable:achievement']
        ),
        'as'         => config('admin.route.prefix') . '.',
    ],
    function () {
        // Main Achievements Resource
        Route::resource('achievements', AchievementsController::class);

        // Store User Achievement
        Route::post('/store-user-achievement', [AchievementLevelsModuleController::class, 'store'])
            ->name('store-user-achievement');

        // Get Achievement Levels (AJAX)
        Route::get('/get-achievement-levels/{achievementId}', [AchievementLevelsModuleController::class, 'getAchievementLevels'])
            ->name('get-achievement-levels');

        // View Page Redirect
        Route::get('/get-view-page', [AchievementLevelsModuleController::class, 'viewPage'])
            ->name('get-view-page');

        // User Achievement Levels Resource
        Route::resource('user-achievement-levels', UserAchievementLevelController::class);

        // Achievement Dedicate (Gift a Badge)
        Route::resource('achievement-dedicate', AchievementDedicateController::class);

        // Gift Achievements
        Route::resource('gift-achievements', UserGiftAchievementController::class);

        // Gift Achievement Type (Gift type = 7)
        Route::resource('gift-achievment', GiftTypeController::class);

        // Gift Achievement Actions
        Route::post('postAddGiftAchievement', [GiftAchievementController::class, 'postAddGiftAchievement'])
            ->name('postAddGiftAchievement');
        Route::post('postAddGiftAchievementLevel', [GiftAchievementController::class, 'postAddGiftAchievementLevel'])
            ->name('postAddGiftAchievementLevel');
        Route::post('posteditGiftAchievementLevel', [GiftAchievementController::class, 'posteditGiftAchievementLevel'])
            ->name('posteditGiftAchievementLevel');

        // Achievement Levels (nested under achievement)
        Route::prefix('achievements-levels/{achievement_id}')->group(function () {
            Route::get('/', [AchievementsLevelsController::class, 'index']);
            Route::get('/create', [AchievementsLevelsController::class, 'create']);
            Route::post('/', [AchievementsLevelsController::class, 'store']);
            Route::get('/{id}', [AchievementsLevelsController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{id}/edit', [AchievementsLevelsController::class, 'edit'])->where('id', '[0-9]+');
            Route::put('/{id}', [AchievementsLevelsController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [AchievementsLevelsController::class, 'destroy'])->where('id', '[0-9]+');
        });
    }
);
