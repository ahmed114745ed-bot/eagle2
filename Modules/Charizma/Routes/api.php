<?php

namespace Modules\Charizma\Http\Services;


use Illuminate\Http\Request;
use Modules\Charizma\Http\Controllers\CharizmaController;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth:sanctum','appFeatureEnable:charizma'])->group(function () {
    Route::prefix('charisma')->group(function () {
        Route::post('/change-status', [CharizmaController::class,'changeStatus']);
        Route::get('/{owner_id}/room', [CharizmaController::class,'extraDataInRoom']);
        Route::post('/reset', 'CharizmaController@reset');
//        Route::post('/remove-user-when-leave-mic/{user_id}/{room_id}/room', [UserCharismaService::class,'RemoveUserRoomWhenLeaveMic']);
//        Route::post('/sendGift/{room_id}/{user_id}/{earned_coins}/room', [UserCharismaService::class,'AddTotalEarnedCoinsInUserRoom']);
    });
});
