<?php

namespace Utd\Gifts\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Utd\Gifts\DTOs\SendGiftDTO;
use Utd\Gifts\Entities\Gift;

/**
 * يُطلق بعد إرسال الهدية بنجاح
 */
class GiftSent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly SendGiftDTO $dto,
        public readonly Gift $gift,
        public readonly mixed $sender,
        public readonly Collection $logs,
        public readonly int $totalPrice
    ) {}

    // Helper methods
    public function isRoomGift(): bool
    {
        return $this->dto->isRoomGift();
    }

    public function isMomentGift(): bool
    {
        return $this->dto->isMomentGift();
    }

    public function isReelGift(): bool
    {
        return $this->dto->isReelGift();
    }

    public function getReceiverIds(): array
    {
        return $this->dto->receiverIds;
    }

    public function getPricePerReceiver(): int
    {
        return $this->totalPrice / count($this->dto->receiverIds);
    }
}
