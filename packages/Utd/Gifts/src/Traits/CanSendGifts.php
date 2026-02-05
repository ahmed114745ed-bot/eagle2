<?php

namespace Utd\Gifts\Traits;

use Utd\Gifts\DTOs\SendGiftDTO;
use Utd\Gifts\Contracts\GiftSenderInterface;
use Illuminate\Support\Collection;

trait CanSendGifts
{
    /**
     * Get the gift sender service
     */
    protected function giftSender(): GiftSenderInterface
    {
        return app(GiftSenderInterface::class);
    }

    /**
     * Send gift in room
     */
    protected function sendRoomGift(
        int $giftId,
        int $senderId,
        array $receiverIds,
        int $quantity,
        int $roomId,
        ?int $pkId = null,
        ?int $cpId = null
    ): Collection {
        $dto = SendGiftDTO::forRoom($giftId, $senderId, $receiverIds, $quantity, $roomId, $pkId, $cpId);
        return $this->giftSender()->send($dto);
    }

    /**
     * Send gift in moment
     */
    protected function sendMomentGift(
        int $giftId,
        int $senderId,
        int $receiverId,
        int $quantity,
        int $momentId
    ): Collection {
        $dto = SendGiftDTO::forMoment($giftId, $senderId, $receiverId, $quantity, $momentId);
        return $this->giftSender()->send($dto);
    }

    /**
     * Send gift in reel
     */
    protected function sendReelGift(
        int $giftId,
        int $senderId,
        int $receiverId,
        int $quantity,
        int $reelId
    ): Collection {
        $dto = SendGiftDTO::forReel($giftId, $senderId, $receiverId, $quantity, $reelId);
        return $this->giftSender()->send($dto);
    }

    /**
     * Send gift to profile
     */
    protected function sendProfileGift(
        int $giftId,
        int $senderId,
        int $receiverId,
        int $quantity
    ): Collection {
        $dto = SendGiftDTO::forProfile($giftId, $senderId, $receiverId, $quantity);
        return $this->giftSender()->send($dto);
    }

    /**
     * Check if can send gift
     */
    protected function canSendGift(SendGiftDTO $dto): bool
    {
        return $this->giftSender()->canSend($dto);
    }
}
