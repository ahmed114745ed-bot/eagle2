<?php

namespace App\Contracts;

use App\Models\User;

interface UpdateUserWhenSendGiftContract
{
    public function send(int $totalCoins, User $senderUser);

    public function sendFromBagAndRemoveGift(int $totalCoins, User $senderUser, int $giftId, int $number);

    public function update(int $totalCoins, User $receivedUser);

    public function updateReceivedLevels(User $receivedUser);

    public function updateUsers(int $totalCoins, array $userIds);

    public function getSenderLevel($totalDiamondSend, $totalDiamond, int $subSenderLevel);

    public function getReceiverLevel($totalDiamondReceived, $totalDiamond, int $subReceiverLevel);

    public function getRoomLevel($total);

    public function getRoomLevelDetails($total);
}
