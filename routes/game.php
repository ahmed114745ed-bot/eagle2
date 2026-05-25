<?php

use App\Http\Controllers\Api\V1\LeaderCCgameController;
use App\Http\Controllers\Api\V1\NewLeaderCCGameController;
use App\Http\Controllers\Api\V1\UtdGameController;

Route::middleware(['auth:sanctum', 'checkLatestToken', 'userBan', 'ip', 'generalBan', 'update.last.seen'])
    ->group(function () {

        Route::prefix('game')->group(function () {
            // Route::get('getUserInfo', [\App\Http\Controllers\Api\V1\JoyPlayController::class, 'getUserInfo']);
            // Route::post('submitFlow', [\App\Http\Controllers\Api\V1\JoyPlayController::class, 'submitFlow']);
        });
    });

Route::post('update-room-count-zego', [\App\Http\Controllers\Api\V1\Room\EnteranceController::class, 'updateRoomCountFromZego2'])
    ->middleware('throttle:zego-room-count');
//Route::post('update-room-count-zego-2', [\App\Http\Controllers\Api\V1\Room\EnteranceController::class, 'updateRoomCountFromZego2']);


// BAISHUN games
Route::prefix('baishun')->middleware('verify.signature')->group(function () {
    Route::post('get_unique_id', [\App\Http\Controllers\Api\V1\BaishunGameController::class, 'getUserUniqueId']);
    Route::post('get-user-info', [\App\Http\Controllers\Api\V1\BaishunGameController::class, 'get_user_info']);
    Route::post('get-sstoken', [\App\Http\Controllers\Api\V1\BaishunGameController::class, 'obtianSstoken']);
    Route::post('update-sstoken', [\App\Http\Controllers\Api\V1\BaishunGameController::class, 'obtianSstoken']);
    Route::get('get_unique_id', [\App\Http\Controllers\Api\V1\BaishunGameController::class, 'getUserUniqueId']);
    Route::get('get-user-info', [\App\Http\Controllers\Api\V1\BaishunGameController::class, 'get_user_info']);
    Route::get('get-sstoken', [\App\Http\Controllers\Api\V1\BaishunGameController::class, 'obtianSstoken']);
    Route::get('update-sstoken', [\App\Http\Controllers\Api\V1\BaishunGameController::class, 'obtianSstoken']);
    Route::post('change-balance', [\App\Http\Controllers\Api\V1\BaishunGameController::class, 'changeBalance']);
});

Route::prefix('leader-cc-game')
    ->withoutMiddleware([\App\Http\Middleware\LogApiRequestResponse::class])
    ->middleware(['verify.game.signature', \App\Http\Middleware\MeasureRequestTimeMiddleware::class, 'throttle:game-balance'])
    ->group(function () {

        Route::post('get-user-info', [LeaderCCgameController::class, 'userInformation']);
        Route::post('change-balance', [LeaderCCgameController::class, 'updateGameCoin']);
        Route::post('make-up-orders', [LeaderCCgameController::class, 'makeUpOrders']);
    });

Route::prefix('utd-game')
    ->withoutMiddleware([\App\Http\Middleware\LogApiRequestResponse::class])
    ->middleware(['verify.utd.signature', \App\Http\Middleware\MeasureRequestTimeMiddleware::class])
    ->group(function () {

        Route::post('get-user-info', [UtdGameController::class, 'getUserInfo']);
        Route::post('change-balance', [UtdGameController::class, 'changeBalance']);
        Route::post('make-up-orders', [UtdGameController::class, 'makeUpOrders']);
    });




Route::prefix('webhook')
    ->group(function () {

        // Section 1: Generate game launch URL (requires authenticated user)
        Route::post('url-games', [NewLeaderCCGameController::class, 'urlGames'])->middleware(['auth:sanctum']);


        Route::post('mic-seats', [NewLeaderCCGameController::class, 'usersUpMic']);
        Route::post('user-info', [NewLeaderCCGameController::class, 'userInfo']);
        Route::post('sit-down', [NewLeaderCCGameController::class, 'sitDown']);
        Route::post('stand-up', [NewLeaderCCGameController::class, 'standUp']);
        Route::post('game-start', [NewLeaderCCGameController::class, 'gameStart']);
        Route::post('game-end', [NewLeaderCCGameController::class, 'gameEnd']);
    });
