<?php

use App\Helpers\UserCommon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Chat\Http\Controllers\ChatMessagesController;
use Modules\Chat\Http\Controllers\ChatReactsController;
use Modules\Chat\Http\Controllers\ChatRoomController;
use Modules\Chat\Http\Controllers\PinToTopController;
use Modules\Chat\Http\Controllers\PusherController;

Route::post('/puhser-edit-user', [PusherController::class, 'edit_user']);
Route::get('user-status/{id}',   [PusherController::class,'user_status']);

Route::middleware(['auth:sanctum', 'verified','generalBan','userBan','localization'])->group(function () {
    //Chat Room
    Route::resource('/Chat-room', ChatRoomController::class);
    Route::post('/Chat-room/accept-request', [ChatRoomController::class,'accept_request']);
    Route::get('/close-chat', [ChatRoomController::class,'close_Chat']);
    Route::resource('/Chat-PinToTop', PinToTopController::class);

    //Chat Message
    Route::resource('/Chat-Message', ChatMessagesController::class);
    Route::post('/delete-Chat-Message', [ChatMessagesController::class,'deleteForAll']);
    Route::post('/delete-Chat-Message-ForMe', [ChatMessagesController::class,'deleteForMe']);
    Route::resource('/Chat-Message-React', ChatReactsController::class);
    Route::post('/find-user', [ChatRoomController::class,'find_user']);
    Route::post('/invite-room', [ChatRoomController::class,'inviteRoom']);
});

