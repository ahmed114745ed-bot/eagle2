<?php

namespace App\Services\Null;

use App\Contracts\GiftsContract;
use Illuminate\Support\Collection;


class NullGiftsService implements GiftsContract
{
    /**
     * @param int $userId
     * @return Collection
     */
    public function getUserGifts($userId)
    {
        return collect();
    }

    /**
     * @param int $senderId
     * @param int $receiverId
     * @param int $giftId
     * @param int $quantity
     * @param array $options
     * @return false
     */
    public function sendGift($senderId, $receiverId, $giftId, $quantity = 1, array $options = [])
    {
        return false;
    }

    /**
     * @param int|null $categoryId
     * @return Collection
     */
    public function getGiftsByCategory($categoryId = null)
    {
        return collect();
    }

    /**
     * @param array $filters
     * @return Collection
     */
    public function getGiftLogs(array $filters = [])
    {
        return collect();
    }

    /**
     * @param int $giftId
     * @return null
     */
    public function getGift($giftId)
    {
        return null;
    }

    /**
     * @return Collection
     */
    public function getAllGifts()
    {
        return collect();
    }

    /**
     * @return Collection
     */
    public function getGiftCategories()
    {
        return collect();
    }

    /**
     * @param int $userId
     * @param int $giftId
     * @param int $quantity
     * @return false
     */
    public function canSendGift($userId, $giftId, $quantity = 1)
    {
        return false;
    }

    /**
     * @param string $type
     * @param array $filters
     * @return Collection
     */
    public function getGiftRankings($type, array $filters = [])
    {
        return collect();
    }

    public function createGiftLog(array $data)
    {
        return null;
    }
}
