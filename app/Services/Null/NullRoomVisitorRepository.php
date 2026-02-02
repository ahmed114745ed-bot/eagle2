<?php

namespace App\Services\Null;

use App\Contracts\RoomVisitorRepositoryContract;

class NullRoomVisitorRepository implements RoomVisitorRepositoryContract
{
    public function getByUser($id)
    {
        return collect();
    }

    public function getByRoom($roomId)
    {
        return collect();
    }

    public function addVisitor($userId, $roomId)
    {
        return null;
    }

    public function removeVisitor($userId, $roomId)
    {
        return 0;
    }
}
