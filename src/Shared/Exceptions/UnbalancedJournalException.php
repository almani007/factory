<?php

namespace Shared\Exceptions;

use Exception;

class UnbalancedJournalException extends Exception
{
    public function __construct(string $message = 'Journal entry is not balanced', int $code = 400, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}