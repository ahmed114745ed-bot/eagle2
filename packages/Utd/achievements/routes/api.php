<?php

use Illuminate\Support\Facades\Route;
use Utd\Achievements\Http\Controllers\AchievementController;
use Utd\Achievements\Http\Controllers\AchievementLevelController;

/*
|--------------------------------------------------------------------------
| Achievement Package API Routes
|--------------------------------------------------------------------------
*/

$middleware = config('achievements.routes.middleware', ['auth:sanctum']);
$prefix = config('achievements.routes.prefix', 'api');

Route::middleware($middleware)->prefix($prefix)->group(function () {

    Route::prefix('achievement')->group(function () {
        Route::get('/{id}', [AchievementController::class, 'getAll']);
        Route::get('/', [AchievementController::class, 'getAll']);
        Route::get('/user/{id}', [AchievementLevelController::class, 'show']);
    });

    Route::post('/user-achievement-select', [AchievementController::class, 'achievementSelect']);
    Route::get('/achievement-all', [AchievementController::class, 'getAll']);
    Route::get('/achievements-details/{id?}', [AchievementController::class, 'getDetails']);
    Route::get('/achievements-picked/{id?}', [AchievementController::class, 'getAllSelect']);
});
