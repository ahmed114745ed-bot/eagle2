<?php

use Illuminate\Support\Facades\Route;
use Utd\Room\Http\Controllers\Admin\RoomController;
use Utd\Room\Http\Controllers\Admin\RoomMicController;
use Utd\Room\Http\Controllers\Admin\RoomVipController;
use Utd\Room\Http\Controllers\Admin\RoomTargetController;
use Utd\Room\Http\Controllers\Admin\RoomSettingsController;
use Utd\Room\Http\Controllers\Admin\RoomGiftTargetController;
use Utd\Room\Http\Controllers\Admin\BanRoomsController;
use Utd\Room\Http\Controllers\Admin\BackgroundController;
use Utd\Room\Http\Controllers\Admin\RequestBackgroundImageController;
use Utd\Room\Http\Controllers\Admin\RoomCategoryController;
use Utd\Room\Http\Controllers\Admin\LiveRoomController;
use Utd\Room\Http\Controllers\Admin\CustomZegoMessageController;
use App\Admin\Controllers\GroupChatController;
use Utd\Room\Entities\Room;

/*
|--------------------------------------------------------------------------
| Room Web Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RoomServiceProvider within a group which
| contains the "web" middleware group.
|
*/

Route::group(
    [
        'prefix' => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
        ],
        'as' => config('admin.route.prefix') . '.',
    ],
    function () {
        // Room Resource Routes
        Route::resource('rooms', RoomController::class, [
            'names' => [
                'index' => 'rooms'
            ]
        ]);
        Route::get('rooms/microphones', [RoomController::class, 'getRoomsMicrophones']);
        Route::get('rooms/{room}/microphones', [RoomController::class, 'getRoomMicrophones']);
        Route::resource('live-rooms', LiveRoomController::class);

        // Room Management Routes
        Route::post('rooms/{id}/remove-admin', [RoomController::class, 'removeAdmin'])->name('rooms.remove-admin');
        Route::post('rooms/{room}/add-visitor', [RoomController::class, 'addVisitor']);
        Route::post('rooms/{room}/kick-visitor', [RoomController::class, 'kickVisitor']);
        Route::post('/rooms/{room}/unban-visitor', [RoomController::class, 'unbanVisitor']);
        Route::post('get-users', [RoomController::class, 'getUsers'])->name('get.users');
        Route::put('rooms/{room}/info', [RoomController::class, 'updateBasicInfo'])->name('rooms.basic_update');
        Route::put('rooms/{id}/update-pin-status', [RoomController::class, 'updatePinStatus']);
        Route::post('rooms/{room}/pin', function (Room $room) {
            $room->update(['pin' => !$room->pin]);
            return response()->json(['success' => true, 'message' => 'Pin updated successfully']);
        })->name('rooms.pin');
        Route::get('rooms/{id}/image', [GroupChatController::class, 'getRoomImage'])->name('rooms.image');

        // Room Category Routes
        Route::resource('categories', RoomCategoryController::class);

        // Background Routes
        Route::resource('backgrounds', BackgroundController::class);
        Route::resource('/request-background-image', RequestBackgroundImageController::class);

        // Room Mic Routes
        Route::get('room-mic/{room_id}/', [RoomMicController::class, 'index']);

        // Room Settings & Targets
        Route::resource('room-settings', RoomSettingsController::class);
        Route::resource('room-vips', RoomVipController::class);
        Route::resource('room-target', RoomTargetController::class);
        Route::resource('room-gift-targets', RoomGiftTargetController::class);

        // Room Bans
        Route::get('/bans-rooms', [BanRoomsController::class, 'index']);

        // Custom Zego Messages
        Route::resource('custom-zego-messages', CustomZegoMessageController::class);

        // Filter
        Route::get('filter-rooms', [RoomController::class, 'filterRooms'])->name('filter-rooms');
    }
);
