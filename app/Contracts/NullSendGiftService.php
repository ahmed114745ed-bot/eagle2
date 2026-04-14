<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

class NullSendGiftService implements SendGiftServiceContract
{
    public function sendGift($number, $room, $gift, $senderUser, $receivedUser, $isPlay = 0, $isPK = 0, $totalPrice = null, $platformObtain = null)
    {
        return null;
    }

    public function sendGift3($number, $room, $gift, $senderUser, Collection $receivedUsers, $isPlay = 0, $totalPrice = null, $isPk = false, array $cpIds = null, $sourceType = null, $type = null)
    {
        return null;
    }

    public function updateFamilyLevelForReceiver(Collection $users, $totalCoinsPerUser): bool
    {
        return false;
    }

    public function updatePkScoresAndSendToZegoJob($pk, $userId, $roomId, $receivedIds, $giftPrice, $room)
    {
        return null;
    }

    public function updatePkScoresAndSendToZegoJob2($pk, $receivedIds, $giftPrice, $room): array
    {
        return [];
    }
}
