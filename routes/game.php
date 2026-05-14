<?php

use App\Http\Controllers\Api\V1\LeaderCCgameController;
use App\Http\Controllers\Api\V1\NewLeaderCCGameController;
use App\Http\Controllers\Api\V1\UtdGameController;



Route::post('update-room-count-zego', [\App\Http\Controllers\Api\V1\Room\EnteranceController::class, 'updateRoomCountFromZego2']);




Route::prefix('leader-cc-game')
    ->withoutMiddleware([\App\Http\Middleware\LogApiRequestResponse::class])
    ->middleware(['verify.game.signature', \App\Http\Middleware\MeasureRequestTimeMiddleware::class])
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

        Route::post('url-games', [NewLeaderCCGameController::class, 'urlGames'])->middleware(['auth:sanctum']);


        Route::post('mic-seats', [NewLeaderCCGameController::class, 'usersUpMic']);
        Route::post('user-info', [NewLeaderCCGameController::class, 'userInfo']);
        Route::post('sit-down', [NewLeaderCCGameController::class, 'sitDown']);
        Route::post('stand-up', [NewLeaderCCGameController::class, 'standUp']);
        Route::post('game-start', [NewLeaderCCGameController::class, 'gameStart']);
        Route::post('game-end', [NewLeaderCCGameController::class, 'gameEnd']);
    });
