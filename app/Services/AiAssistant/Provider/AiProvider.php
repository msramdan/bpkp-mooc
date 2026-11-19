<?php

namespace App\Services\AiAssistant\Provider;

use Closure;

interface AiProvider
{
    /**
     * Kirim percakapan ke model. $toolExecutor(name, input) => [isError, jsonString]
     * dijalankan backend untuk setiap tool call yang diminta model.
     *
     * @param  list<array{role:string, content:string}>  $messages
     * @param  Closure(string, mixed): array{0:bool, 1:string}  $toolExecutor
     *
     * @throws ProviderException
     */
    public function chat(string $systemPrompt, array $messages, array $tools, Closure $toolExecutor, array $options): ProviderResult;

    /**
     * Cek koneksi & API key. Tidak menghabiskan token (memakai Models API).
     *
     * @return array{ok:bool, message:string, models:list<array{id:string, name:string}>}
     */
    public function testConnection(array $options): array;

    public function name(): string;
}
