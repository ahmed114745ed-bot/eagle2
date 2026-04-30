<?php

namespace Utd\Gifts\DTOs;

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
