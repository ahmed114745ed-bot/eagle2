<?php

use App\Http\Controllers\Api\V1\ChargeController;
use Illuminate\Support\Facades\Route;
use Utd\Room\Http\Controllers\Api\BackgroundController;
use Utd\Room\Http\Controllers\Api\EnteranceController;
use Utd\Room\Http\Controllers\Api\MicrophoneController;
use Utd\Room\Http\Controllers\Api\RequestBackgroundImageController;
use Utd\Room\Http\Controllers\Api\RoomCategoryController;
use Utd\Room\Http\Controllers\Api\RoomController;

/*
|--------------------------------------------------------------------------
| Room API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RoomServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// Public routes (no auth required)
Route::prefix('api')->group(function () {
    Route::post('update-room-count', [EnteranceController::class, 'updateRoomCountFromPusher']);
    Route::post('update-room-count-pusher', [EnteranceController::class, 'updateRoomCountFromPusher_new']);
    Route::get('update-zego-agora', [EnteranceController::class, 'libraryAgoraZego']);
});

// Authenticated routes
Route::prefix('api')->middleware(['auth:sanctum', 'checkLatestToken', 'generalBan', 'userBan', 'update.last.seen', 'localization'])->group(function () {
    // Check room
    Route::post('check-room', [RoomController::class, 'check_room']);

    Route::prefix('rooms')->group(function () {
        Route::get('/room-user', [RoomController::class, 'userRooms']);
        Route::get('/mine', [RoomController::class, 'mine']);
        Route::get('/user/{id}', [RoomController::class, 'userRoom']);
        Route::get('/', [RoomController::class, 'index']);
        Route::get('/live-rooms', [RoomController::class, 'getAllLiveRooms']);
        Route::get('/game-rooms', [RoomController::class, 'gameRoom']);
        Route::post('/create', [RoomController::class, 'store']);
        Route::get('/{id}', [RoomController::class, 'show'])->where('id', '[0-9]+');
        Route::get('/{owner_id}/extra-data', [RoomController::class, 'extraRoomData']);
        Route::get('/extra-data', [RoomController::class, 'extraDataRoom']);
        Route::post('/{owner_id}/send-private-comment', [RoomController::class, 'sendPrivateComment']);
        Route::post('charge_dollar_for_owner', [ChargeController::class, 'charge_co_for_owner']);
        Route::post('{room_id}/disable-writing', [RoomController::class, 'disable_writing']);
        Route::post('pk/change-image', [RoomController::class, 'changeRoomImage']);
        Route::post('/{id}/edit', [EnteranceController::class, 'updateRoomVisitorsupdate']);
        Route::post('firstOfRoom', [RoomController::class, 'firstOfRoom']);
        Route::post('admins', [RoomController::class, 'getAdmins']);
        Route::post('request-background-image', [RequestBackgroundImageController::class, 'RequestBackgroundImage']);
        Route::post('remove_pass', [RoomController::class, 'removeRoomPass']);
        Route::post('room_background_list', [BackgroundController::class, 'roomBackground']);
        Route::post('quit_room', [RoomController::class, 'quit_room_2']);
        Route::post('getRoomUsers', [RoomController::class, 'getRoomUsers']);
        Route::post('add_admin_to_room', [RoomController::class, 'is_admin']);
        Route::post('kick_out_of_room', [RoomController::class, 'out_room']);
        Route::post('remove_admin', [RoomController::class, 'remove_admin']);
        Route::post('black-list', [RoomController::class, 'blackList']);
        Route::post('remove-block', [RoomController::class, 'removeBlock']);
        Route::post('add-block', [RoomController::class, 'addBlock']);
        Route::post('{Room}/comment_status', [RoomController::class, 'commentStatus']);
        Route::post('/yellow-banner', [RoomController::class, 'sendComment']);
        Route::post('/check-admin-owner', [RoomController::class, 'adminOwner']);

        // PK routes moved to packages/Utd/Pk/Routes/api.php

        // Microphone routes
        Route::post('liveTime', [MicrophoneController::class, 'lifeTime']);
        Route::post('up-microphone', [MicrophoneController::class, 'upMicrophone2']);
        Route::post('leave-microphone', [MicrophoneController::class, 'goMicrophone2']);
        Route::post('kick_microphone', [MicrophoneController::class, 'kickMicrophone']);
        Route::post('mute_microphone', [MicrophoneController::class, 'mute_microphone2']);
        Route::post('unmute_microphone', [MicrophoneController::class, 'unmute_microphone2']);
        Route::post('lock_microphone_place', [MicrophoneController::class, 'shut_microphone2']);
        Route::post('unlock_microphone_place', [MicrophoneController::class, 'open_microphone2']);
        Route::post('enter_room', [EnteranceController::class, 'enter_room']);
        Route::post('invite-user', [EnteranceController::class, 'invite_user']);
    });

    // Room mode routes
    Route::post('change_room_mode', [RoomController::class, 'changeMode']);
    Route::post('rooms/change-mic-mode', [RoomController::class, 'changeMicMode']);

    // Room countries
    Route::get('/room-countries', [RoomController::class, 'room_countries']);

    // Room categories
    Route::prefix('room_category')->group(function () {
        Route::get('classes', [RoomCategoryController::class, 'allClasses']);
        Route::get('types', [RoomCategoryController::class, 'getTypes']);
        Route::get('types_by_class/{id}', [RoomCategoryController::class, 'getClassChildren']);
    });

    Route::prefix('backgrounds')->group(function () {
        Route::get('/', [BackgroundController::class, 'allBackgrounds']);
        Route::get('/me', [BackgroundController::class, 'allMyBackgrounds']);
        Route::get('/setting', [BackgroundController::class, 'backgroundSetting']);
    });
});
