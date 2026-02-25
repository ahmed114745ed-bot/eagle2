<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;
use Utd\RankingReward\Http\Controllers\RankingRewardController;
use Utd\RankingReward\Http\Controllers\RankingTypeController;
use Utd\RankingReward\Http\Controllers\WinnerRankingController;

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix').'.',
    ],
    function () {
        Route::resource('ranking-types', RankingTypeController::class);
        Route::resource('winner-rankings', WinnerRankingController::class);
        Route::get('rewards/{id}', [RankingTypeController::class, 'getRewards']);

        Route::prefix('ranking-rewards/{ranking_range_id}')->group(function () {
            Route::delete('/{id}', [RankingRewardController::class, 'destroy'])->where('id', '[0-9]+')->name('ranking_rewards.destroy');
        });
    }
);
