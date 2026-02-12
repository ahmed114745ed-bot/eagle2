<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Utd\Achievements\Http\Controllers\web\AchievementDedicateController;
use Utd\Achievements\Http\Controllers\web\AchievementLevelsModuleController;
use Utd\Achievements\Http\Controllers\web\AchievementsController;
use Utd\Achievements\Http\Controllers\web\AchievementsLevelsController;
use Utd\Achievements\Http\Controllers\web\GiftAchiementController;
use Utd\Achievements\Http\Controllers\web\GiftAchievemntController;
use Utd\Achievements\Http\Controllers\web\UserAchievementLevelController;
use Utd\Achievements\Http\Controllers\web\UserGiftAchController;

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix').'.',
    ],
    function () {
        Route::resource('achievements', AchievementsController::class);
        Route::post('/store-user-achievement', [AchievementLevelsModuleController::class, 'store'])->name('store-user-achievement');
        Route::get('/get-achievement-levels/{achievementId}', [AchievementLevelsModuleController::class, 'getAchievementLevels'])->name('get-achievement-levels');
        Route::get('/get-view-page', [AchievementLevelsModuleController::class, 'viewPage'])->name('get-view-page');
        Route::resource('user-achievement-levels', UserAchievementLevelController::class);
        Route::resource('achievement-dedicate', AchievementDedicateController::class);
        // Route::post('postAddGiftAchievementLevel', [GiftAchievemntController::class,'postAddGiftAchievementLevel'])->name('postAddGiftAchievementLevel');
        //  Route::get('achievement-levels/create/{id}', 'AchievementsLevelsController@create')->where('id', '[0-9]+')->name('achievement-levels.create');
        Route::resource('gift-achievements', UserGiftAchController::class);
        Route::resource('gift-achievment', GiftAchiementController::class);
        Route::post('postAddGiftAchievement', [GiftAchievemntController::class, 'postAddGiftAchievemnt'])->name('postAddGiftAchievement');
        Route::post('postAddGiftAchievementLevel', [GiftAchievemntController::class, 'postAddGiftAchievementLevel'])->name('postAddGiftAchievementLevel');
        Route::post('posteditGiftAchievementLevel', [GiftAchievemntController::class, 'posteditGiftAchievementLevel'])->name('posteditGiftAchievementLevel');
        // Route::resource('achievement-levels', AchievementsLevelsController::class,['names'=>['create'=>'achievement-levels.create2']]);

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
