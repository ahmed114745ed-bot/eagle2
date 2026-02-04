<?php

namespace App\Services\Null;

use App\Contracts\NewRoomBoomGiftServiceContract;

class NullNewRoomBoomGiftService implements NewRoomBoomGiftServiceContract
{
    public function sendGift($room, $totalPrice, $userId): void
    {
        // No-op when RoomBoom package is not installed
    }

    public function getOrCreateTotalRoomGift($roomId, $todayStart)
    {
        return null;
    }

    public function oldLevels($totalPrice, $totalRoomGiftId, $userId, &$currentTotal): void
    {
        // No-op when RoomBoom package is not installed
    }
}
