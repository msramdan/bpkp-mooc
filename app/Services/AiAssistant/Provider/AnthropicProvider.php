<?php

namespace App\Services\AiAssistant\Provider;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\APITimeoutException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\BadRequestException;
use Anthropic\Core\Exceptions\InternalServerException;
use Anthropic\Core\Exceptions\NotFoundException;
use Anthropic\Core\Exceptions\OverloadedException;
use Anthropic\Core\Exceptions\PermissionDeniedException;
use Anthropic\Core\Exceptions\RateLimitException;
use Anthropic\Core\Exceptions\AnthropicException;
use App\Services\AiAssistant\AiAssistantSettings;
use Closure;
use Psr\Http\Client\ClientInterface;
use Throwable;

/**
 * Provider Anthropic Claude via SDK resmi (anthropic-ai/sdk).
 *
 * API key dibaca dari settings terenkripsi tepat sebelum request dan tidak pernah
 * dicatat. Loop tool use dijalankan di backend: model hanya bisa memanggil fungsi
 * whitelist, eksekusinya dilakukan oleh $toolExecutor dengan user dari sesi login.
 */
class AnthropicProvider implements AiProvider
{
    private const MAX_TOOL_ITERATIONS = 5;

    private const FALLBACK_BETA = 'server-side-fallback-2026-07-01';

    public function __construct(
        private readonly AiAssistantSettings $settings,
        private readonly ?ClientInterface $transporter = null,
    ) {}

    public function name(): string
    {
        return 'anthropic';
    }

    public function chat(string $systemPrompt, array $messages, array $tools, Closure $toolExecutor, array $options): ProviderResult
    {
        $client = $this->client((int) ($options['timeout'] ?? 60));
        $model = (string) $options['model'];
        $effort = $options['effort'] ?? null;

        $inputTokens = 0;
        $outputTokens = 0;
        $cacheRead = 0;
        $toolCalls = [];
        $iterations = 0;

        try {
            while (true) {
                $iterations++;

                $params = [
                    'maxTokens' => (int) $options['max_tokens'],
                    'messages' => $messages,
                    'model' => $model,
                    'system' => [['type' => 'text', 'text' => $systemPrompt, 'cacheControl' => ['type' => 'ephemeral']]],
                    'tools' => $tools,
                    // Tolakan kebijakan pada model utama dialihkan server ke model cadangan bawaan.
                    'fallbacks' => 'default',
                    'betas' => [self::FALLBACK_BETA],
                ];

                if (! empty($options['user_hash'])) {
                    $params['metadata'] = ['userID' => $options['user_hash']];
                }

                if (is_string($effort) && $effort !== '') {
                    $params['outputConfig'] = ['effort' => $effort];
                }

                $response = $client->beta->messages->create(...$params);

                $inputTokens += (int) $response->usage->inputTokens + (int) ($response->usage->cacheCreationInputTokens ?? 0);
                $outputTokens += (int) $response->usage->outputTokens;
                $cacheRead += (int) ($response->usage->cacheReadInputTokens ?? 0);

                if ($response->stopReason !== 'tool_use' || $iterations >= self::MAX_TOOL_ITERATIONS) {
                    break;
                }

                $results = [];
                foreach ($response->content as $block) {
                    if (($block->type ?? null) !== 'tool_use') {
                        continue;
                    }

                    $toolCalls[] = (string) $block->name;
                    [$isError, $payload] = $toolExecutor((string) $block->name, $block->input);

                    $results[] = [
                        'type' => 'tool_result',
                        'toolUseID' => $block->id,
                        'content' => $payload,
                        'isError' => $isError,
                    ];
                }

                if ($results === []) {
                    break;
                }

                // Kirim balik seluruh blok asisten (termasuk thinking) tanpa diubah.
                $messages[] = ['role' => 'assistant', 'content' => $response->content];
                $messages[] = ['role' => 'user', 'content' => $results];
            }
        } catch (Throwable $e) {
            throw $this->translate($e);
        }

        $text = '';
        foreach ($response->content as $block) {
            if (($block->type ?? null) === 'text') {
                $text .= $block->text;
            }
        }

        $refusalCategory = null;
        if ($response->stopReason === 'refusal') {
            $refusalCategory = $response->stopDetails->category ?? 'unknown';
            $text = 'Maaf, saya tidak dapat membantu permintaan tersebut. Silakan ajukan pertanyaan lain seputar pembelajaran Anda di MOOC.';
        } elseif ($response->stopReason === 'tool_use') {
            $text = trim($text) !== '' ? $text : 'Maaf, pertanyaan ini membutuhkan terlalu banyak langkah. Coba ajukan pertanyaan yang lebih spesifik.';
        } elseif ($response->stopReason === 'max_tokens') {
            $text = rtrim($text)."\n\n_(Jawaban terpotong karena batas panjang jawaban.)_";
        }

        return new ProviderResult(
            text: trim($text),
            model: (string) $response->model,
            stopReason: $response->stopReason,
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
            cacheReadTokens: $cacheRead,
            toolCalls: array_values(array_unique($toolCalls)),
            iterations: $iterations,
            refusalCategory: $refusalCategory,
        );
    }

    public function testConnection(array $options): array
    {
        try {
            $client = $this->client(min(30, (int) ($options['timeout'] ?? 30)));
            $page = $client->models->list(limit: 100);

            $models = [];
            foreach ($page->pagingEachItem() as $model) {
                // Hanya model aktif; model deprecated/retired tidak ditawarkan.
                if (($model->lifecycle ?? 'active') !== 'active' || $model->deprecatedAt !== null) {
                    continue;
                }
                $models[] = ['id' => $model->id, 'name' => $model->displayName];
            }

            return [
                'ok' => true,
                'message' => __('Koneksi ke Anthropic berhasil. :n model aktif tersedia.', ['n' => count($models)]),
                'models' => $models,
            ];
        } catch (Throwable $e) {
            $error = $e instanceof ProviderException ? $e : $this->translate($e);

            return ['ok' => false, 'message' => $error->getMessage(), 'models' => []];
        }
    }

    private function client(int $timeout): Client
    {
        $apiKey = $this->settings->apiKey('anthropic');

        if ($apiKey === null || $apiKey === '') {
            throw new ProviderException('missing_api_key', __('API key Anthropic belum dikonfigurasi.'));
        }

        $requestOptions = [
            'timeout' => (float) max(5, $timeout),
            'maxRetries' => 1,
        ];

        if ($this->transporter) {
            $requestOptions['transporter'] = $this->transporter;
        }

        return new Client(apiKey: $apiKey, requestOptions: $requestOptions);
    }

    private function translate(Throwable $e): ProviderException
    {
        if ($e instanceof ProviderException) {
            return $e;
        }

        $status = $e instanceof APIStatusException ? $e->status : null;

        return match (true) {
            $e instanceof AuthenticationException => new ProviderException('auth_error', __('API key Anthropic tidak valid atau sudah dicabut.'), $status),
            $e instanceof PermissionDeniedException => new ProviderException('permission_denied', __('API key tidak memiliki izin untuk model/fitur ini.'), $status),
            $e instanceof NotFoundException => new ProviderException('model_not_found', __('Model AI tidak ditemukan. Periksa nama model di pengaturan.'), $status),
            $e instanceof RateLimitException => new ProviderException('rate_limited', __('Batas penggunaan API Anthropic tercapai. Coba lagi beberapa saat.'), $status),
            $e instanceof BadRequestException => new ProviderException('bad_request', __('Permintaan ke AI ditolak: :msg', ['msg' => $this->safe($e->getAPIMessage() ?? $e->getMessage())]), $status),
            $e instanceof OverloadedException => new ProviderException('overloaded', __('Layanan AI sedang sibuk. Coba lagi beberapa saat.'), $status),
            $e instanceof InternalServerException => new ProviderException('provider_error', __('Layanan AI sedang bermasalah. Coba lagi beberapa saat.'), $status),
            $e instanceof APIStatusException => new ProviderException('provider_error', __('Layanan AI mengembalikan error (:code).', ['code' => $status]), $status),
            $e instanceof APITimeoutException => new ProviderException('timeout', __('Layanan AI tidak merespons tepat waktu.')),
            $e instanceof APIConnectionException => new ProviderException('connection_error', __('Tidak dapat terhubung ke layanan AI.')),
            $e instanceof AnthropicException => new ProviderException('provider_error', __('Terjadi kesalahan pada layanan AI.')),
            default => new ProviderException('internal_error', __('Terjadi kesalahan saat memproses jawaban AI.')),
        };
    }

    /** Pesan error tanpa rahasia & dipendekkan. */
    private function safe(string $message): string
    {
        $message = preg_replace('/sk-ant-[A-Za-z0-9_\-]+/', '[redacted]', $message) ?? $message;

        return mb_substr($message, 0, 200);
    }
}
