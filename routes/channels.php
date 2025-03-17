<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Room;
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('presence-room-{roomId}', function ($user, $roomId) {
    return ['id' => $user->id, 'name' => $user->name]; // Must return user details
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    // \Illuminate\Support\Facades\Log::info('test - '.$user->name .' : '. $user->id);

    return (int) $user->id === (int) $id;
});
Broadcast::channel('room-{roomId}', function ($user, $roomId) {
    return  $roomId;
});

Broadcast::channel('room-{roomId}-{userId}', function ($user, $roomId, $userId) {
    // \Illuminate\Support\Facades\Log::info($user->name .' : '. $user->id);
    return  $userId == $user->id;
});


Broadcast::channel('enter-user-room', function ($user) {
    return $user;
});

Broadcast::channel('test-channel', function () {
    return true; // No authentication required
});
