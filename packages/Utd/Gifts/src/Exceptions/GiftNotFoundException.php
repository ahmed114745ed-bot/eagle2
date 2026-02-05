<?php

namespace Utd\Gifts\Exceptions;

use Exception;

class GiftNotFoundException extends Exception
{
    public function __construct(string $message = "Gift not found", int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
