<?php

namespace App\Services\AiAssistant\Provider;

use Closure;

/**
 * Provider tiruan untuk uji lokal/otomatis TANPA API key. Tidak memanggil layanan luar.
 * Tetap menjalankan tool whitelist asli sehingga alur data, isolasi user, kuota, dan
 * log bisa diuji end-to-end. Hanya aktif bila APP_ENV local/testing.
 */
class FakeProvider implements AiProvider
{
    public function __construct(private readonly string $emulates = 'anthropic') {}

    public function name(): string
    {
        return 'fake-'.$this->emulates;
    }

    public function chat(string $systemPrompt, array $messages, array $tools, Closure $toolExecutor, array $options): ProviderResult
    {
        $last = end($messages);
        $question = mb_strtolower(is_string($last['content'] ?? null) ? $last['content'] : '');

        if (str_contains($question, '[fake-error]')) {
            throw new ProviderException('timeout', 'Layanan AI tidak merespons tepat waktu.');
        }

        if (str_contains($question, '[fake-slow]')) {
            usleep(1_500_000);
        }

        $tool = match (true) {
            str_contains($question, 'sertifikat') => 'list_my_certificates',
            str_contains($question, 'progres') => 'list_my_courses',
            str_contains($question, 'hari ini') || str_contains($question, 'selesaikan') => 'list_my_pending_tasks',
            str_contains($question, 'nilai') || str_contains($question, 'kuis') => 'list_my_quiz_results',
            str_contains($question, 'profil') => 'get_my_profile',
            str_contains($question, 'cara') || str_contains($question, 'fitur') => 'get_mooc_help',
            default => 'list_my_courses',
        };

        $input = $tool === 'get_mooc_help' ? ['topic' => 'ai_assistant'] : [];
        [, $payload] = $toolExecutor($tool, $input);
        $data = json_decode($payload, true) ?: [];

        $text = match ($tool) {
            'list_my_courses' => $this->courses($data),
            'list_my_certificates' => 'Anda memiliki **'.($data['jumlah_sertifikat'] ?? 0).'** sertifikat.',
            'list_my_pending_tasks' => 'Ada **'.($data['kursus_berlangsung'] ?? 0).'** kursus yang masih berlangsung.',
            'list_my_quiz_results' => 'Anda sudah mengirim **'.($data['jumlah'] ?? 0).'** kuis/survei.',
            'get_my_profile' => 'Profil: **'.($data['nama'] ?? '-').'** ('.($data['email'] ?? '-').')',
            default => (string) ($data['panduan'] ?? 'Panduan tidak tersedia.'),
        };

        return new ProviderResult(
            text: "_[Mode uji lokal – bukan jawaban Claude]_\n\n".$text,
            model: 'fake-local',
            stopReason: 'end_turn',
            inputTokens: 120,
            outputTokens: 40,
            cacheReadTokens: 0,
            toolCalls: [$tool],
        );
    }

    public function testConnection(array $options): array
    {
        return [
            'ok' => true,
            'message' => 'Mode uji lokal aktif untuk '.$this->emulates.' (tanpa koneksi ke provider).',
            'models' => [['id' => 'claude-haiku-5-5', 'name' => 'claude-haiku-5-5 (lokal)'], ['id' => 'claude-sonnet-5-5', 'name' => 'claude-sonnet-5-5 (lokal)']],
            'balance' => $this->emulates === 'sumopod' ? 0.5 : null,
        ];
    }

    private function courses(array $data): string
    {
        $lines = ['Anda mengikuti **'.($data['jumlah_kursus'] ?? 0).'** kursus:'];
        foreach ($data['kursus'] ?? [] as $c) {
            $lines[] = '- '.$c['judul'].' — '.$c['progres_persen'].'% ('.$c['status'].')';
        }

        return implode("\n", $lines);
    }
}
