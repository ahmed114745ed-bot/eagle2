<?php

namespace App\Contracts;

use App\Models\User;

class NullUpdateUserWhenSendGift implements UpdateUserWhenSendGiftContract
{
    public function send(int $totalCoins, User $senderUser)
    {
        return $senderUser;
    }

    public function sendFromBagAndRemoveGift(int $totalCoins, User $senderUser, int $giftId, int $number)
    {
        return $senderUser;
    }

    public function update(int $totalCoins, User $receivedUser)
    {
        return null;
    }

    public function updateReceivedLevels(User $receivedUser)
    {
        return $receivedUser;
    }

    public function updateUsers(int $totalCoins, array $userIds)
    {
        return null;
    }

    public function getSenderLevel($totalDiamondSend, $totalDiamond, int $subSenderLevel)
    {
        return 0;
    }

    public function getReceiverLevel($totalDiamondReceived, $totalDiamond, int $subReceiverLevel)
    {
        return 0;
    }

    public function getRoomLevel($total)
    {
        return 0;
    }

    public function getRoomLevelDetails($total)
    {
        return null;
    }
}
