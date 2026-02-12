<?php

/*
|--------------------------------------------------------------------------
| api Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Utd\Achievements\Http\Controllers\AchievementController;
use Utd\Achievements\Http\Controllers\AchievementLevelController;

Route::middleware(['auth:sanctum', 'update.last.seen'])->group(function () {
    Route::prefix('achievement')->group(function () {
        Route::get('/{id}', [AchievementController::class, 'get_all']);
        Route::get('/', [AchievementController::class, 'get_all']);
        Route::get('/user/{id}', [AchievementLevelController::class, 'show']);
    });

    Route::post('/user-achievement-select', [AchievementController::class, 'achivement_select']);
    Route::get('/achievement-all', [AchievementController::class, 'get_all']);
    Route::get('/achievements-details/{id?}', [AchievementController::class, 'get_details']);
    Route::get('/achievements-picked/{id?}', [AchievementController::class, 'get_all_select']);

});
