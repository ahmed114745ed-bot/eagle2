<?php

namespace App\Services\Null;

use App\Contracts\RoomRepositoryContract;

class NullRoomRepository implements RoomRepositoryContract
{
    public function findById($id)
    {
        return null;
    }

    public function findRoomUser($userId, $withoutAppends = true)
    {
        return null;
    }

    public function findRoomUserEnableAudio($userId)
    {
        return null;
    }

    public function findRoom($roomId)
    {
        return null;
    }

    public function getRooms($ids)
    {
        return collect();
    }

    public function all($req, $ids = [])
    {
        return collect();
    }

    public function updateRoomStatus($userId, $isAvailable)
    {
        return null;
    }

    public function updateMicRoom($room, $mic)
    {
        return false;
    }

    public function findUserRoom($ownerId, $selectRow = "*")
    {
        return null;
    }

    public function findTypeUserRoom($ownerId, $type = 'audio', $selectRow = "*")
    {
        return null;
    }

    public function findUserRoomById($ownerId, $selectRow = "*")
    {
        return null;
    }
}
