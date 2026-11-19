<?php

namespace App\Services\AiAssistant\Provider;

use App\Services\AiAssistant\AiAssistantSettings;
use InvalidArgumentException;

/**
 * Membuat instance provider berdasarkan nama. Provider aktif ditentukan sekali di awal
 * setiap request, sehingga pergantian provider di tengah request tidak mempengaruhi
 * request yang sedang berjalan.
 */
class ProviderFactory
{
    public function __construct(private readonly AiAssistantSettings $settings) {}

    public function make(?string $name = null): AiProvider
    {
        // Provider tiruan hanya untuk uji lokal tanpa API key; production selalu provider asli.
        if (config('ai_assistant.fake_provider') && app()->environment(['local', 'testing'])) {
            return new FakeProvider($name ?? $this->settings->activeProvider());
        }

        return match ($name ?? $this->settings->activeProvider()) {
            'anthropic' => new AnthropicProvider($this->settings),
            'sumopod' => new SumoPodProvider($this->settings),
            default => throw new InvalidArgumentException('Provider AI tidak dikenal.'),
        };
    }
}
