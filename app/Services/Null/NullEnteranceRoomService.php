<?php

namespace App\Services\Null;

use App\Contracts\EnteranceRoomContract;
use Illuminate\Http\Request;

class NullEnteranceRoomService implements EnteranceRoomContract
{
    public function updateRoomCountFromPusher(Request $request)
    {
        return null;
    }

    public function updateRoomCountFromZego(Request $request)
    {
        return null;
    }

    public function updateRoomCountFromZego2(Request $request)
    {
        return null;
    }

    public function updateRoomCountFromAgora(Request $request)
    {
        return null;
    }

    public function handleLeaveCp($user, $room)
    {
        return null;
    }

    public function removeUserCpInRoom(mixed $userId): void
    {
        //
    }

    public function sendCpLovelyMessage($room, $user)
    {
        return null;
    }

    public function cpMapJson($indices): string|false
    {
        return false;
    }

    public function enterRoom($user, $request, $room_pass, $room)
    {
        return null;
    }

    public function makeRequestInviteRoom($user, $request)
    {
        return null;
    }

    public function enterLiveRoom($user, Request $request, $roomPass, $room)
    {
        return null;
    }
}
