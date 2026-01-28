<?php

use App\Http\Controllers\Api\V1\LeaderCCgameController;

Route::middleware(['auth:sanctum', 'checkLatestToken', 'userBan', 'ip', 'generalBan', 'update.last.seen'])
    ->group(function () {

        Route::prefix('game')->group(function () {
            // Route::get('getUserInfo', [\App\Http\Controllers\Api\V1\JoyPlayController::class, 'getUserInfo']);
            // Route::post('submitFlow', [\App\Http\Controllers\Api\V1\JoyPlayController::class, 'submitFlow']);
        });
    });

Route::post('update-room-count-zego', [\App\Http\Controllers\Api\V1\Room\EnteranceController::class, 'updateRoomCountFromZego2']);
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

// Test signature endpoint (remove in production)
Route::post('leader-cc-game/test-signature', function (\Illuminate\Http\Request $request) {
    $key = config('games.leader_CC_game_key');
    
    // Test with different key values
    $keys = [
        'config_key' => $key,
        '303' => '303',
        '18uarRTK57' => '18uarRTK57',
    ];
    
    $results = [];
    
    foreach ($keys as $name => $testKey) {
        $rawString = $request->gameId . $request->uid . $request->token . $request->roomId . $testKey;
        $expectedSign = md5($rawString);
        
        $results[$name] = [
            'key' => $testKey,
            'rawString' => $rawString,
            'expectedSign' => $expectedSign,
            'match' => strtolower($expectedSign) === strtolower($request->sign),
        ];
    }
    
    return response()->json([
        'receivedSign' => $request->sign,
        'body' => $request->all(),
        'results' => $results,
    ]);
});

/*

curl -X POST http://127.0.0.1:8000/api/leader-cc-game/test-signature \
  -H "Content-Type: application/json" \
  -d '{"gameId":"101","uid":"20","token":"85|Iw9T7FBg4EnCUWXsYJnUAQ8vfBNnpaXqm77ccQKt46402b51","roomId":"Lingxian","sign":"68380444b392707738c99a0f91a9bb17"}' 2>&1
  
  */