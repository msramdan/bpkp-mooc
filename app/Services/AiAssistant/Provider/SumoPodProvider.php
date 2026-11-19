<?php

namespace App\Services\AiAssistant\Provider;

use App\Services\AiAssistant\AiAssistantSettings;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Provider SumoPod (AI gateway). Mengikuti dokumentasi resmi SumoPod:
 *  - POST {base}/v1/chat/completions  (format OpenAI-compatible, Bearer API key)
 *  - GET  {base}/v1/models             (daftar model)
 *  - GET  {base}/v2/user/info          (saldo = max_budget - spend)
 *
 * Aturan sama dengan provider Anthropic: model hanya bisa memanggil tool whitelist,
 * tool dijalankan backend dengan user dari sesi, API key tidak pernah dicatat.
 */
class SumoPodProvider implements AiProvider
{
    private const MAX_TOOL_ITERATIONS = 5;

    public function __construct(private readonly AiAssistantSettings $settings) {}

    public function name(): string
    {
        return 'sumopod';
    }

    public function chat(string $systemPrompt, array $messages, array $tools, Closure $toolExecutor, array $options): ProviderResult
    {
        $model = (string) $options['model'];
        $payload = [
            'model' => $model,
            'max_tokens' => (int) $options['max_tokens'],
            'messages' => [['role' => 'system', 'content' => $systemPrompt], ...$messages],
            'tools' => $this->toolsToOpenAi($tools),
            'tool_choice' => 'auto',
        ];

        if (! empty($options['user_hash'])) {
            $payload['user'] = $options['user_hash'];
        }

        $inputTokens = 0;
        $outputTokens = 0;
        $cacheRead = 0;
        $cost = null;
        $toolCalls = [];
        $iterations = 0;
        $data = [];
        $message = [];

        while (true) {
            $iterations++;
            $response = $this->send(fn (PendingRequest $http) => $http->post($this->url('/v1/chat/completions'), $payload), (int) ($options['timeout'] ?? 60));
            $data = $response->json() ?? [];

            $usage = (array) ($data['usage'] ?? []);
            $cached = (int) ($usage['prompt_tokens_details']['cached_tokens'] ?? 0);
            $inputTokens += max(0, (int) ($usage['prompt_tokens'] ?? 0) - $cached);
            $outputTokens += (int) ($usage['completion_tokens'] ?? 0);
            $cacheRead += $cached;

            // Biaya aktual dari gateway (LiteLLM) bila tersedia.
            $headerCost = $response->header('x-litellm-response-cost');
            if (is_numeric($headerCost)) {
                $cost = ($cost ?? 0) + (float) $headerCost;
            }

            $choice = (array) ($data['choices'][0] ?? []);
            $message = (array) ($choice['message'] ?? []);
            $calls = array_values(array_filter((array) ($message['tool_calls'] ?? []), fn ($c) => is_array($c) && isset($c['id'], $c['function']['name'])));

            if ($calls === [] || $iterations >= self::MAX_TOOL_ITERATIONS) {
                break;
            }

            $payload['messages'][] = [
                'role' => 'assistant',
                'content' => $message['content'] ?? null,
                'tool_calls' => $calls,
            ];

            foreach ($calls as $call) {
                $name = (string) $call['function']['name'];
                $toolCalls[] = $name;
                $rawArgs = $call['function']['arguments'] ?? '{}';
                $args = is_array($rawArgs) ? $rawArgs : json_decode((string) $rawArgs, true);

                [$isError, $result] = is_array($args)
                    ? $toolExecutor($name, $args)
                    : [true, json_encode(['error' => 'Argumen fungsi bukan JSON yang valid.'])];

                $payload['messages'][] = [
                    'role' => 'tool',
                    'tool_call_id' => (string) $call['id'],
                    'content' => $isError ? '[ERROR] '.$result : $result,
                ];
            }
        }

        $finish = $data['choices'][0]['finish_reason'] ?? null;
        $text = $this->contentToText($message['content'] ?? '');
        $refusal = null;

        if ($finish === 'content_filter' || ! empty($message['refusal'])) {
            $refusal = 'content_filter';
            $text = 'Maaf, saya tidak dapat membantu permintaan tersebut. Silakan ajukan pertanyaan lain seputar pembelajaran Anda di MOOC.';
        } elseif (! empty($message['tool_calls']) && trim($text) === '') {
            $text = 'Maaf, pertanyaan ini membutuhkan terlalu banyak langkah. Coba ajukan pertanyaan yang lebih spesifik.';
        } elseif ($finish === 'length') {
            $text = rtrim($text)."\n\n_(Jawaban terpotong karena batas panjang jawaban.)_";
        }

        return new ProviderResult(
            text: trim($text),
            model: (string) ($data['model'] ?? $model),
            stopReason: $finish,
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
            cacheReadTokens: $cacheRead,
            toolCalls: array_values(array_unique($toolCalls)),
            iterations: $iterations,
            refusalCategory: $refusal,
            costUsd: $cost,
        );
    }

    public function testConnection(array $options): array
    {
        try {
            $timeout = min(30, (int) ($options['timeout'] ?? 30));
            $models = $this->send(fn (PendingRequest $http) => $http->get($this->url('/v1/models')), $timeout)->json('data') ?? [];

            $list = collect($models)
                ->filter(fn ($m) => is_array($m) && isset($m['id']))
                ->map(fn ($m) => ['id' => (string) $m['id'], 'name' => (string) $m['id']])
                ->sortBy('id')
                ->values()
                ->all();

            $balance = null;
            try {
                $info = $this->send(fn (PendingRequest $http) => $http->get($this->url('/v2/user/info')), $timeout)->json() ?? [];
                $max = $info['max_budget'] ?? ($info['user_info']['max_budget'] ?? null);
                $spend = $info['spend'] ?? ($info['user_info']['spend'] ?? null);
                if (is_numeric($max) && is_numeric($spend)) {
                    $balance = round((float) $max - (float) $spend, 4);
                }
            } catch (Throwable) {
                // Saldo opsional; koneksi tetap dianggap berhasil.
            }

            $message = __('Koneksi ke SumoPod berhasil. :n model tersedia.', ['n' => count($list)]);
            if ($balance !== null) {
                $message .= ' '.__('Sisa saldo: $:b', ['b' => number_format($balance, 4)]);
            }

            return ['ok' => true, 'message' => $message, 'models' => $list, 'balance' => $balance];
        } catch (ProviderException $e) {
            return ['ok' => false, 'message' => $e->getMessage(), 'models' => []];
        }
    }

    /**
     * Kirim request dengan API key SumoPod. Retry sekali untuk gangguan sementara.
     *
     * @param  Closure(PendingRequest): Response  $call
     */
    private function send(Closure $call, int $timeout): Response
    {
        $apiKey = $this->settings->apiKey('sumopod');

        if ($apiKey === null || $apiKey === '') {
            throw new ProviderException('missing_api_key', __('API key SumoPod belum dikonfigurasi.'));
        }

        $http = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(10)
            ->timeout(max(5, $timeout));

        $attempt = 0;
        while (true) {
            $attempt++;
            try {
                $response = $call($http);
            } catch (ConnectionException $e) {
                if ($attempt < 2) {
                    usleep(500_000);

                    continue;
                }
                $timedOut = str_contains(strtolower($e->getMessage()), 'timed out') || str_contains(strtolower($e->getMessage()), 'timeout');

                throw new ProviderException($timedOut ? 'timeout' : 'connection_error', $timedOut
                    ? __('Layanan AI (SumoPod) tidak merespons tepat waktu.')
                    : __('Tidak dapat terhubung ke SumoPod.'));
            }

            if ($response->successful()) {
                return $response;
            }

            if ($attempt < 2 && in_array($response->status(), [429, 500, 502, 503, 504], true)) {
                usleep(800_000);

                continue;
            }

            throw $this->translate($response);
        }
    }

    private function translate(Response $response): ProviderException
    {
        $status = $response->status();
        $raw = (string) ($response->json('error.message') ?? $response->json('detail') ?? $response->body());
        $msg = $this->safe($raw);
        $lower = strtolower($raw);

        return match (true) {
            $status === 401 => new ProviderException('auth_error', __('API key SumoPod tidak valid atau sudah dicabut.'), $status),
            $status === 402 || str_contains($lower, 'budget') || str_contains($lower, 'insufficient') || str_contains($lower, 'balance') => new ProviderException('insufficient_balance', __('Saldo / budget SumoPod habis. Silakan top up di dashboard SumoPod.'), $status),
            $status === 403 => new ProviderException('permission_denied', __('API key SumoPod tidak memiliki izin untuk model ini.'), $status),
            $status === 404 || (str_contains($lower, 'model') && (str_contains($lower, 'invalid') || str_contains($lower, 'not found') || str_contains($lower, 'does not exist'))) => new ProviderException('model_not_found', __('Model tidak tersedia di SumoPod. Periksa nama model di pengaturan.'), $status),
            $status === 429 => new ProviderException('rate_limited', __('Batas penggunaan SumoPod tercapai. Coba lagi beberapa saat.'), $status),
            $status >= 500 => new ProviderException('provider_error', __('Layanan SumoPod sedang bermasalah. Coba lagi beberapa saat.'), $status),
            default => new ProviderException('bad_request', __('Permintaan ke SumoPod ditolak: :msg', ['msg' => $msg]), $status),
        };
    }

    private function toolsToOpenAi(array $tools): array
    {
        return array_map(fn (array $t) => [
            'type' => 'function',
            'function' => [
                'name' => $t['name'],
                'description' => $t['description'],
                'parameters' => $t['inputSchema'],
            ],
        ], $tools);
    }

    private function contentToText(mixed $content): string
    {
        if (is_string($content)) {
            return $content;
        }

        if (is_array($content)) {
            return implode('', array_map(fn ($p) => is_array($p) ? (string) ($p['text'] ?? '') : (string) $p, $content));
        }

        return '';
    }

    private function url(string $path): string
    {
        return rtrim((string) config('ai_assistant.providers.sumopod.base_url'), '/').$path;
    }

    private function safe(string $message): string
    {
        $message = preg_replace('/(sk-[A-Za-z0-9_\-]{6,})/', '[redacted]', $message) ?? $message;

        return mb_substr(trim($message), 0, 200);
    }
}
