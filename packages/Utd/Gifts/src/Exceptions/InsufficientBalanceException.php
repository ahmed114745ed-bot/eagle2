<?php

namespace Utd\Gifts\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function __construct(string $message = 'Insufficient balance', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
