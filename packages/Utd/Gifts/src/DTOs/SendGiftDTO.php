<?php

namespace Utd\Gifts\DTOs;

use Illuminate\Support\Collection;

class SendGiftDTO
{
    public function __construct(
        public readonly int $giftId,
        public readonly int $senderId,
        public readonly array $receiverIds,
        public readonly int $quantity,
        public readonly string $sourceType,
        public readonly ?int $sourceId = null,
        public readonly array $metadata = []
    ) {}

    // ============ Factory Methods ============
    
    /**
     * إرسال هدية في روم
     */
    public static function forRoom(
        int $giftId,
        int $senderId,
        array $receiverIds,
        int $quantity,
        int $roomId,
        ?int $pkId = null,
        ?int $cpId = null
    ): self {
        return new self(
            giftId: $giftId,
            senderId: $senderId,
            receiverIds: $receiverIds,
            quantity: $quantity,
            sourceType: 'room',
            sourceId: $roomId,
            metadata: [
                'room_id' => $roomId,
                'pk_id' => $pkId,
                'cp_id' => $cpId,
            ]
        );
    }

    /**
     * إرسال هدية في Moment
     */
    public static function forMoment(
        int $giftId,
        int $senderId,
        int $receiverId,
        int $quantity,
        int $momentId
    ): self {
        return new self(
            giftId: $giftId,
            senderId: $senderId,
            receiverIds: [$receiverId],
            quantity: $quantity,
            sourceType: 'moment',
            sourceId: $momentId,
            metadata: ['moment_id' => $momentId]
        );
    }

    /**
     * إرسال هدية في Reel
     */
    public static function forReel(
        int $giftId,
        int $senderId,
        int $receiverId,
        int $quantity,
        int $reelId
    ): self {
        return new self(
            giftId: $giftId,
            senderId: $senderId,
            receiverIds: [$receiverId],
            quantity: $quantity,
            sourceType: 'reel',
            sourceId: $reelId,
            metadata: ['reel_id' => $reelId]
        );
    }

    /**
     * إرسال هدية على البروفايل
     */
    public static function forProfile(
        int $giftId,
        int $senderId,
        int $receiverId,
        int $quantity
    ): self {
        return new self(
            giftId: $giftId,
            senderId: $senderId,
            receiverIds: [$receiverId],
            quantity: $quantity,
            sourceType: 'profile',
            sourceId: $receiverId,
            metadata: []
        );
    }

    // ============ Helper Methods ============
    
    public function getTotalReceivers(): int
    {
        return count($this->receiverIds);
    }

    public function getRoomId(): ?int
    {
        return $this->metadata['room_id'] ?? null;
    }

    public function getMomentId(): ?int
    {
        return $this->metadata['moment_id'] ?? null;
    }

    public function getReelId(): ?int
    {
        return $this->metadata['reel_id'] ?? null;
    }

    public function getPkId(): ?int
    {
        return $this->metadata['pk_id'] ?? null;
    }

    public function getCpId(): ?int
    {
        return $this->metadata['cp_id'] ?? null;
    }

    public function isRoomGift(): bool
    {
        return $this->sourceType === 'room';
    }

    public function isMomentGift(): bool
    {
        return $this->sourceType === 'moment';
    }

    public function isReelGift(): bool
    {
        return $this->sourceType === 'reel';
    }

    public function toArray(): array
    {
        return [
            'gift_id' => $this->giftId,
            'sender_id' => $this->senderId,
            'receiver_ids' => $this->receiverIds,
            'quantity' => $this->quantity,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'metadata' => $this->metadata,
        ];
    }
}
