<?php

namespace Utd\Gifts\Exceptions;

use Exception;

class VipLevelRequiredException extends Exception
{
    public function __construct(string $message = "VIP level required", int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
