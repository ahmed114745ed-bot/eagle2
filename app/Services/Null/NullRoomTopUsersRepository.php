<?php

namespace App\Services\Null;

use App\Contracts\RoomTopUsersRepositoryContract;

class NullRoomTopUsersRepository implements RoomTopUsersRepositoryContract
{
    public function findOrCreate($roomId, $userId)
    {
        return null;
    }

    public function getRoomTopUser($roomId, $with = [])
    {
        return null;
    }
}
