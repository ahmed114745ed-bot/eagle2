<?php

namespace App\Contracts;

use App\Models\User;

interface RoomGameContract
{
    public function updateRoomCoins(User $user, $coins);
    public function CalculateRoomSalaries($room);
}
