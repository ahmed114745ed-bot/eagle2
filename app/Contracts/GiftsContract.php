<?php

namespace App\Contracts;


interface GiftsContract
{
    /**
     * 
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getUserGifts($userId);

    /**
     * 
     * @param int $senderId
     * @param int $receiverId
     * @param int $giftId
     * @param int $quantity
     * @param array $options
     * @return mixed
     */
    public function sendGift($senderId, $receiverId, $giftId, $quantity = 1, array $options = []);

    /**
     * 
     * @param int|null $categoryId
     * @return \Illuminate\Support\Collection
     */
    public function getGiftsByCategory($categoryId = null);

    /**
     * 
     * @param array $filters
     * @return mixed
     */
    public function getGiftLogs(array $filters = []);

    /**
     * 
     * @param int $giftId
     * @return mixed
     */
    public function getGift($giftId);

    /**
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getAllGifts();

    /**
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getGiftCategories();

    /**
     * 
     * @param int $userId
     * @param int $giftId
     * @param int $quantity
     * @return bool
     */
    public function canSendGift($userId, $giftId, $quantity = 1);

    /**
     * 
     * @param string $type
     * @param array $filters
     * @return mixed
     */
    public function getGiftRankings($type, array $filters = []);

    /**
     * 
     * @param array $data
     * @return mixed
     */
    public function createGiftLog(array $data);
}
