<?php

namespace App\Services\AiAssistant\Provider;

final class ProviderResult
{
    /**
     * @param  list<string>  $toolCalls
     */
    public function __construct(
        public readonly string $text,
        public readonly string $model,
        public readonly ?string $stopReason,
        public readonly int $inputTokens,
        public readonly int $outputTokens,
        public readonly int $cacheReadTokens,
        public readonly array $toolCalls = [],
        public readonly int $iterations = 1,
        public readonly ?string $refusalCategory = null,
        /** Biaya aktual dari provider/gateway bila tersedia (USD). */
        public readonly ?float $costUsd = null,
    ) {}
}
