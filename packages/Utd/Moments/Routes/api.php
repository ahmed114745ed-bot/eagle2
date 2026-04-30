<?php

use Illuminate\Support\Facades\Route;
use Utd\Moments\Http\Controllers\MomentController;
use Utd\Moments\Http\Controllers\MomentUserCommentController;
use Utd\Moments\Http\Controllers\MomentUserGiftsController;
use Utd\Moments\Http\Controllers\MomentUserLikesController;
use Utd\Moments\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan', 'update.last.seen'])->group(
    function () {
        Route::apiResource('/moment', MomentController::class)->middleware('ban.user.actions:moment');
        Route::apiResource('moment/{moment_id}/comment', MomentUserCommentController::class);
        Route::apiResource('moment/{moment_id}/like', MomentUserLikesController::class);
        Route::post('moment/{moment_id}/report', [ReportController::class, 'store']);

        Route::middleware('package:gift')->group(function () {
            Route::get('moments/users/{id}/gifts', [MomentUserGiftsController::class, 'userGift']);
            Route::apiResource('moment/{moment_id}/gift/', MomentUserGiftsController::class);
            Route::apiResource('moment/{moment_id}/users/gifts/', MomentUserGiftsController::class);
            Route::get('moments/{id}/gifts', [MomentUserGiftsController::class, 'getGifts']);
        });
    }
);
