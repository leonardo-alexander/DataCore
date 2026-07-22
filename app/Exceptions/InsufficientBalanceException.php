<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public function __construct(string $message = 'Your wallet balance is not enough for this action.')
    {
        parent::__construct($message);
    }
}
