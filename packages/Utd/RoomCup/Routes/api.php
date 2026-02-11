<?php

use Illuminate\Support\Facades\Route;
use Utd\RoomCup\Http\Controllers\Api\RoomCupController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => 'api/Utd/room-cup',
    'middleware' => ['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan', 'localization', 'update.last.seen']
], function () {
    Route::get('/report/{room_id}', [RoomCupController::class, 'myReward']);
    Route::get('/history/{room_id}', [RoomCupController::class, 'roomAdministratorManagement']);
    Route::get('/cup-target', [RoomCupController::class, 'cupTargetHtml']);
});
