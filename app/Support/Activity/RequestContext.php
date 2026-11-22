<?php

namespace App\Support\Activity;

use Illuminate\Support\Str;

/**
 * Konteks per request: satu request_id untuk menautkan log halaman dengan
 * semua perubahan data yang terjadi di request yang sama.
 */
class RequestContext
{
    private ?string $requestId = null;

    public function requestId(): string
    {
        return $this->requestId ??= (string) Str::uuid();
    }

    public function reset(): void
    {
        $this->requestId = null;
    }
}
