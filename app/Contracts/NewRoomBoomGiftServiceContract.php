<?php

namespace App\Contracts;

interface NewRoomBoomGiftServiceContract
{
    /**
     * Send gift and handle room boom logic
     *
     * @param mixed $room
     * @param int $totalPrice
     * @param int $userId
     * @return void
     * @throws \Throwable
     */
    public function sendGift($room, $totalPrice, $userId): void;

    /**
     * Get or create total room gift record for today
     *
     * @param int $roomId
     * @param mixed $todayStart
     * @return mixed
     */
    public function getOrCreateTotalRoomGift($roomId, $todayStart);

    /**
     * Process old levels and distribute gift amounts
     *
     * @param int $totalPrice
     * @param int $totalRoomGiftId
     * @param int $userId
     * @param int &$currentTotal
     * @return void
     */
    public function oldLevels($totalPrice, $totalRoomGiftId, $userId, &$currentTotal): void;
}
