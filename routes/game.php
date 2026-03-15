<?php

use App\Http\Controllers\Api\V1\LeaderCCgameController;

Route::middleware(['auth:sanctum', 'checkLatestToken', 'userBan', 'ip', 'generalBan', 'update.last.seen'])
    ->group(function () {

        Route::prefix('game')->group(function () {
            // Route::get('getUserInfo', [\App\Http\Controllers\Api\V1\JoyPlayController::class, 'getUserInfo']);
            // Route::post('submitFlow', [\App\Http\Controllers\Api\V1\JoyPlayController::class, 'submitFlow']);
        });
    });

//Route::post('update-room-count-zego', [\App\Http\Controllers\Api\V1\Room\EnteranceController::class, 'updateRoomCountFromZego2']);
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
    ->middleware(['verify.game.signature', \App\Http\Middleware\MeasureRequestTimeMiddleware::class])
    ->group(function () {

    Route::post('get-user-info', [LeaderCCgameController::class, 'userInformation']);
    Route::post('change-balance', [LeaderCCgameController::class, 'updateGameCoin']);
    Route::post('make-up-orders', [LeaderCCgameController::class, 'makeUpOrders']);
});
