<?php

use Illuminate\Support\Facades\Route;
use Utd\CP\Http\Controllers\CpController;
use Utd\CP\Http\Controllers\CpRelationController;
use Utd\CP\Http\Controllers\WeeklyCpController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'localization', 'update.last.seen'])->group(function () {
    Route::get('/cp-relations', [CpRelationController::class, 'index']);
    Route::post('/make-cp-request', [CpController::class, 'makeRequestCp']);
    Route::get('/get-cp-request', [CpController::class, 'getRequestCp']);
    Route::post('/respond-request', [CpController::class, 'RespondRequest']);
    Route::get('/cp-ranking', [CpController::class, 'CpRanking']);
    Route::get('/cp-list', [CpController::class, 'cpList']);
    Route::post('/buy-sets', [CpController::class, 'extendCard']);
    Route::get('/cp-profile', [CpController::class, 'cpProfile']);
    Route::get('/cp-levels-gifts', [CpController::class, 'cpLevels']);

    Route::get('/cp-user-list', [CpController::class, 'cpUserList']);

});

Route::prefix('weekly-cp')->middleware(['auth:sanctum', 'localization'])->group(function () {
    Route::get('pervious-Winners', [WeeklyCpController::class, 'perviousWeeklyCpWinners']);
    Route::get('details', [WeeklyCpController::class, 'weeklyCpDetails']);
    Route::get('top-users', [WeeklyCpController::class, 'topUsers']);
    Route::get('top-pervious-winner', [WeeklyCpController::class, 'topOnePerviousWeeklyCp']);
    Route::get('user_details', [WeeklyCpController::class, 'userDetails']);

});
