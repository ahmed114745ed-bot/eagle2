<?php

namespace App\Services\Null;

use App\Contracts\RoomGameContract;
use App\Models\User;

class NullRoomGameService implements RoomGameContract
{
    public function updateRoomCoins(User $user, $coins)
    {
        //
    }

    public function CalculateRoomSalaries($room)
    {
        //
    }
}
