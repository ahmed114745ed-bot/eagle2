<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface EnteranceRoomContract
{
    public function updateRoomCountFromPusher(Request $request);
    public function updateRoomCountFromZego(Request $request);
    public function updateRoomCountFromZego2(Request $request);
    public function updateRoomCountFromAgora(Request $request);
    public function handleLeaveCp($user, $room);
    public function removeUserCpInRoom(mixed $userId): void;
    public function sendCpLovelyMessage($room, $user);
    public function cpMapJson($indices): string|false;
    public function enterRoom($user, $request, $room_pass, $room);
    public function makeRequestInviteRoom($user, $request);
    public function enterLiveRoom($user, Request $request, $roomPass, $room);
}
