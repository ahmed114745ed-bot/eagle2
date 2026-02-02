<?php

namespace App\Services\Null;

use App\Contracts\RoomServiceContract;
use App\Models\User;

class NullRoomService implements RoomServiceContract
{
    public function getAllRooms($request)
    {
        return collect();
    }

    public function findRoomByUser($userId)
    {
        return null;
    }

    public function findRoom($id)
    {
        return null;
    }

    public function findRoomByUserAndType($userId, $type)
    {
        return null;
    }

    public function createRoom(array $data, User $user)
    {
        return null;
    }

    public function updateRoom($roomId, array $data)
    {
        return null;
    }

    public function getRoomDetails($roomId)
    {
        return null;
    }

    public function getRoomAdmins($roomId)
    {
        return collect();
    }

    public function updateRoomStatus($userId, bool $isAvailable)
    {
        return null;
    }

    public function getRoomsByGame($gameId)
    {
        return collect();
    }

    public function toggleWriting($roomId)
    {
        return null;
    }

    public function changePassword($roomId, ?string $password = null)
    {
        return null;
    }

    public function isOwnerOrAdmin(User $user, $roomId): bool
    {
        return false;
    }

    public function addAdmin($roomId, $userId)
    {
        return null;
    }

    public function removeAdmin($roomId, $userId)
    {
        return null;
    }

    public function getRoomBackground($room): string
    {
        return '';
    }
}
