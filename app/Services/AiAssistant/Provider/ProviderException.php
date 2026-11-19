<?php

namespace App\Services\AiAssistant\Provider;

use RuntimeException;

/**
 * Error provider dengan pesan yang aman ditampilkan / dicatat (tanpa API key / payload).
 */
class ProviderException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $safeMessage,
        public readonly ?int $httpStatus = null,
    ) {
        parent::__construct($safeMessage);
    }
}
