<?php

namespace Utd\Gifts\Events;

use Utd\Gifts\DTOs\SendGiftDTO;
use Utd\Gifts\Entities\Gift;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُطلق قبل إرسال الهدية
 * يمكن إلغاء الإرسال بإرجاع false
 */
class GiftSending
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly SendGiftDTO $dto,
        public readonly Gift $gift,
        public readonly mixed $sender,
        public readonly int $totalPrice
    ) {}
}
