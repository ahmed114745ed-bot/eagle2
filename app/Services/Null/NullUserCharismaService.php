<?php

namespace App\Services\Null;

use App\Contracts\UserCharismaServiceContract;
use Illuminate\Support\Collection;

class NullUserCharismaService implements UserCharismaServiceContract
{
    public function roomCharisma($room_id)
    {
        return [];
    }

    public function RemoveUserRoomWhenLeaveMic($userId, $roomId)
    {
        return null;
    }

    public function addTotalEarnedCoinsInUserRoom($room, array $userIds, $earnedCoins = null): false|array
    {
        return [];
    }

    public function addTotalEarnedCoinsInUserRoom2($room, array $userIds, $earnedCoins = null): false|array
    {
        return [];
    }

    public function getUserIdWithPosition($microphones): Collection
    {
        return collect();
    }

    public function getUserIdWithPosition2($room)
    {
        return collect();
    }

    public function removeRoomCharisma(int $roomId)
    {
        // No-op
    }

    public function resetUserCharisma(int $userId, int $roomId)
    {
        // No-op
    }

    public function getUserResetData($microphones, array $userIds)
    {
        return [];
    }

    public function getUserResetData2($room, array $userIds)
    {
        return [];
    }
}
