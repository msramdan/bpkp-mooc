<?php

namespace App\Services\Exceptions;

use RuntimeException;

class SurveyAttemptException extends RuntimeException
{
    public function __construct(public readonly string $reason, string $message)
    {
        parent::__construct($message);
    }
}
