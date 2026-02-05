<?php

namespace Utd\Gifts\Services;

use Utd\Gifts\DTOs\SendGiftDTO;
use Utd\Gifts\Entities\Gift;
use App\Models\User;

class GiftValidationService
{
    /**
     * التحققات الإضافية
     */
    public function validate(SendGiftDTO $dto, Gift $gift, User $sender): void
    {
        // يمكن إضافة تحققات إضافية هنا
        // مثل: التحقق من الحظر، القيود الزمنية، إلخ
    }
}
