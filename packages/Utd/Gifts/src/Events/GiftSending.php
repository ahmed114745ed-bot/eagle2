<?php

namespace Utd\Gifts\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Utd\Gifts\DTOs\SendGiftDTO;
use Utd\Gifts\Entities\Gift;

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
