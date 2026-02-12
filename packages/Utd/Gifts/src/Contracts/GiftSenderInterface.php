<?php

namespace Utd\Gifts\Contracts;

use Illuminate\Support\Collection;
use Utd\Gifts\DTOs\SendGiftDTO;
use Utd\Gifts\Entities\Gift;

interface GiftSenderInterface
{
    /**
     * إرسال هدية
     *
     * @return Collection<GiftLog>
     *
     * @throws \Utd\Gifts\Exceptions\InsufficientBalanceException
     * @throws \Utd\Gifts\Exceptions\GiftNotFoundException
     * @throws \Utd\Gifts\Exceptions\VipLevelRequiredException
     */
    public function send(SendGiftDTO $dto): Collection;

    /**
     * التحقق من إمكانية إرسال الهدية
     */
    public function canSend(SendGiftDTO $dto): bool;

    /**
     * حساب السعر الإجمالي
     */
    public function calculateTotalPrice(Gift $gift, int $quantity, int $receiversCount): int;

    /**
     * التحقق من VIP level
     */
    public function checkVipRequirement(Gift $gift, int $userId): bool;
}
