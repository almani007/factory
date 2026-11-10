<?php

namespace App\Shared\Exceptions;

use Exception;

class PeriodClosedException extends Exception
{
    public function __construct(string $message = 'Accounting period is closed', int $code = 400, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}