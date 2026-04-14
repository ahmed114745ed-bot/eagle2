<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface SendGiftServiceContract
{
    public function sendGift($number, $room, $gift, $senderUser, $receivedUser, $isPlay = 0, $isPK = 0, $totalPrice = null, $platformObtain = null);

    public function sendGift3($number, $room, $gift, $senderUser, Collection $receivedUsers, $isPlay = 0, $totalPrice = null, $isPk = false, array $cpIds = null, $sourceType = null, $type = null);

    public function updateFamilyLevelForReceiver(Collection $users, $totalCoinsPerUser): bool;

    public function updatePkScoresAndSendToZegoJob($pk, $userId, $roomId, $receivedIds, $giftPrice, $room);

    public function updatePkScoresAndSendToZegoJob2($pk, $receivedIds, $giftPrice, $room): array;
}
