<?php

namespace App\Modules\Tenancy\Exceptions;

use Exception;

class TenantNotFoundException extends Exception
{
    public function __construct(string $message = 'Tenant not found', int $code = 404, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}