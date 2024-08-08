<?php

Route::middleware(['auth:sanctum', 'checkLatestToken', 'userBan', 'ip', 'generalBan'])
    ->group(function () {

        Route::prefix('game')->group(function () {
            Route::get('getUserInfo', [\App\Http\Controllers\Api\V1\JoyPlayController::class, 'getUserInfo']);
            Route::post('submitFlow', [\App\Http\Controllers\Api\V1\JoyPlayController::class, 'submitFlow']);
        });
    });



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

