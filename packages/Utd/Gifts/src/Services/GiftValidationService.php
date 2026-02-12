<?php

namespace Utd\Gifts\Services;

use Utd\Gifts\DTOs\SendGiftDTO;
use Utd\Gifts\Entities\Gift;

class GiftValidationService
{
    public function validate(SendGiftDTO $dto, Gift $gift, $sender): void {}
}
