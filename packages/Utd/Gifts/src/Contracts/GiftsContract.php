<?php

namespace Utd\Gifts\Contracts;

use Illuminate\Support\Collection;

/**
 * GiftsContract
 *
 * Contract لإدارة عمليات Gifts Service
 */
interface GiftsContract
{
    /**
     * جلب هدايا المستخدم
     *
     * @param  int  $userId
     * @return Collection
     */
    public function getUserGifts($userId);

    /**
     * إرسال هدية
     *
     * @param  int  $senderId
     * @param  int  $receiverId
     * @param  int  $giftId
     * @param  int  $quantity
     * @return mixed
     */
    public function sendGift($senderId, $receiverId, $giftId, $quantity = 1, array $options = []);

    /**
     * التحقق من إمكانية إرسال الهدية
     *
     * @param  int  $userId
     * @param  int  $giftId
     * @param  int  $quantity
     * @return bool
     */
    public function canSendGift($userId, $giftId, $quantity = 1);
}
