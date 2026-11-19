<?php

namespace App\Services\AiAssistant;

use App\Models\User;

/**
 * Lapisan keamanan sebelum & sesudah model AI.
 *
 * Input  : tolak permintaan rahasia sistem (ENV, API key, password DB), source code,
 *          SQL, upaya prompt injection, dan permintaan data user lain.
 * Output : sensor pola rahasia / data pribadi orang lain sebelum dikirim ke user.
 *
 * Ini lapisan pertama; lapisan utama tetap arsitektur: model tidak punya akses DB,
 * hanya fungsi whitelist yang memakai user dari sesi login.
 */
class SecurityFirewall
{
    /** @var array<string, array{0:string, 1:list<string>}> kategori => [alasan, pola regex] */
    private const INPUT_RULES = [
        'secret_request' => [
            'Permintaan informasi rahasia sistem (ENV, API key, kredensial) tidak diizinkan.',
            [
                '/\.env\b/i',
                '/\b(env|environment)\s*(file|variable|var|config)/i',
                '/\bvariabel\s+(lingkungan|env)/i',
                '/\b(api|secret|private|access|app|encryption)[\s_-]?(key|token)\b/i',
                '/\bkunci\s+(api|rahasia|enkripsi)\b/i',
                '/\bsk-ant-/i',
                '/\b(db|database|mysql|server|root|admin|aapanel|ssh|smtp|mail)[\s_-]*(pass(word)?|kata\s*sandi|credential|kredensial|user(name)?)\b/i',
                '/\b(pass(word)?|kata\s*sandi|kredensial|credential)s?\s+(db|database|mysql|server|root|admin|aapanel|ssh|smtp|sistem|aplikasi)\b/i',
                '/\b(hash|bcrypt)\s+(password|kata\s*sandi)/i',
                '/\b(DB_PASSWORD|DB_USERNAME|APP_KEY|ANTHROPIC_API_KEY|MAIL_PASSWORD)\b/i',
                '/\b(konfigurasi|config(uration)?)\s+(server|database|sistem|aplikasi|laravel)\b/i',
            ],
        ],
        'source_code' => [
            'Permintaan source code atau struktur internal sistem tidak diizinkan.',
            [
                '/\b(source\s*code|kode\s*sumber|kode\s*program|script\s*(php|backend))\b/i',
                '/\b(controller|middleware|migration|routes?\.php|web\.php|\.php|kode\s+(php|backend|aplikasi|sistem)|(php|backend)\s+code)\b/i',
                '/\b(struktur|skema|schema)\s+(database|db|tabel|table)\b/i',
                '/\b(nama|daftar|list)\s+(tabel|table|kolom|column)s?\b/i',
                '/\b(information_schema|phpmyadmin|artisan|composer\.json|storage\/logs)\b/i',
            ],
        ],
        'sql_injection' => [
            'Perintah database (SQL) tidak diizinkan.',
            [
                '/\bselect\s+(\*|[\w.`]+(\s*,\s*[\w.`]+)*)\s+from\s+[\w.`]+/i',
                '/\bdelete\s+from\s+[\w.`]+/i',
                '/\bupdate\s+[\w.`]+\s+set\s+[\w.`]+\s*=/i',
                '/\binsert\s+into\s+[\w.`]+/i',
                '/\b(drop|truncate|alter)\s+(table|database|schema)\b/i',
                '/\bunion\s+(all\s+)?select\b/i',
                '/\bor\s+\'?1\'?\s*=\s*\'?1\b|;\s*--|\/\*.*\*\//i',
            ],
        ],
        'other_user_data' => [
            'AI Assistant hanya dapat membahas data akun Anda sendiri.',
            [
                '/\b(data|nilai|progres|sertifikat|email|nip|password|kata\s*sandi|profil|akun)\s+(user|pengguna|peserta|orang|pegawai|akun)\s+(lain|lainnya)\b/i',
                '/\b(semua|seluruh|daftar|list)\s+(user|pengguna|peserta|akun|pegawai)\b/i',
                '/\b(user|pengguna|peserta)\s+(id|dengan\s+id)\s*[:=]?\s*\d+/i',
                '/\b(other|all)\s+(users?|participants?|students?)\b/i',
                '/\b(login|masuk)\s+(sebagai|as)\s+(user|admin|orang|akun)/i',
            ],
        ],
    ];

    private const INJECTION_RULES = [
        '/\b(ignore|disregard|forget|override)\b.{0,30}\b(previous|above|prior|all|system|earlier)\b.{0,20}\b(instructions?|rules?|prompts?|messages?)\b/i',
        '/\b(abaikan|lupakan|langgar|timpa|hapus)\b.{0,30}\b(instruksi|aturan|perintah|prompt|batasan)\b/i',
        '/\b(system\s*prompt|prompt\s*sistem|instruksi\s*sistem|pesan\s*sistem|developer\s*message|hidden\s*instructions?)\b/i',
        '/\b(jailbreak|developer\s*mode|dan\s*mode|god\s*mode|mode\s*(developer|pengembang|admin|tanpa\s*batas))\b/i',
        '/\b(you\s+are\s+now|from\s+now\s+on\s+you|act\s+as|pretend\s+(to\s+be|you)|roleplay\s+as)\b/i',
        '/\b(kamu|anda)\s+(sekarang|mulai\s+sekarang)\s+(adalah|menjadi|berperan)\b/i',
        '/\b(berpura-?pura|berperanlah)\s+(sebagai|menjadi)\b/i',
        '/\b(jawab|jawablah|balas|answer|respond|reply|mode)\b.{0,20}\b(tanpa|without|no)\s+(batasan|filter|sensor|aturan|restrictions?|limits?|rules)\b/i',
        '/<\s*\/?\s*(system|instructions?|admin|assistant)\s*>/i',
        '/\[(system|inst|admin)\]/i',
    ];

    public function __construct(private readonly AiAssistantSettings $settings) {}

    /**
     * @return array{allowed:bool, category:?string, reason:?string, flags:list<string>}
     */
    public function inspectInput(string $text): array
    {
        $maxLength = max(100, $this->settings->int('max_question_length'));

        if (mb_strlen($text) > $maxLength) {
            return $this->block('too_long', __('Pertanyaan terlalu panjang (maksimal :max karakter).', ['max' => $maxLength]));
        }

        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $text)) {
            return $this->block('invalid_input', __('Pertanyaan mengandung karakter yang tidak valid.'));
        }

        $normalized = $this->normalize($text);

        if ($this->settings->bool('prompt_injection_protection_enabled')) {
            foreach (self::INJECTION_RULES as $pattern) {
                if (preg_match($pattern, $normalized)) {
                    return $this->block('prompt_injection', __('Permintaan untuk mengubah aturan atau instruksi AI Assistant tidak diizinkan.'));
                }
            }
        }

        if ($this->settings->bool('security_firewall_enabled')) {
            foreach (self::INPUT_RULES as $category => [$reason, $patterns]) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $normalized)) {
                        return $this->block($category, __($reason));
                    }
                }
            }
        }

        return ['allowed' => true, 'category' => null, 'reason' => null, 'flags' => []];
    }

    /**
     * Sensor output AI: rahasia sistem dan data pribadi yang bukan milik user.
     *
     * @return array{text:string, redacted:bool, flags:list<string>}
     */
    public function sanitizeOutput(string $text, User $user): array
    {
        $flags = [];

        $secretPatterns = [
            'anthropic_key' => '/sk-ant-[A-Za-z0-9_\-]{8,}/',
            'app_key' => '/base64:[A-Za-z0-9+\/=]{20,}/',
            'password_hash' => '/\$2[aby]\$\d{2}\$[.\/A-Za-z0-9]{40,}/',
            'env_assignment' => '/\b(DB_PASSWORD|DB_USERNAME|APP_KEY|ANTHROPIC_API_KEY|MAIL_PASSWORD|AWS_SECRET_ACCESS_KEY)\s*=\s*\S+/i',
            'private_key' => '/-----BEGIN [A-Z ]*PRIVATE KEY-----[\s\S]*?(-----END [A-Z ]*PRIVATE KEY-----|$)/',
        ];

        foreach ($secretPatterns as $flag => $pattern) {
            $text = preg_replace_callback($pattern, function () use (&$flags, $flag): string {
                $flags[] = $flag;

                return '[disensor]';
            }, $text) ?? $text;
        }

        if ($this->settings->bool('sensitive_data_protection_enabled')) {
            $ownEmail = mb_strtolower((string) $user->email);
            $text = preg_replace_callback('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', function (array $m) use (&$flags, $ownEmail): string {
                if (mb_strtolower($m[0]) === $ownEmail) {
                    return $m[0];
                }
                $flags[] = 'foreign_email';

                return '[email disensor]';
            }, $text) ?? $text;

            $ownNip = preg_replace('/\D/', '', (string) $user->nip);
            $text = preg_replace_callback('/\b\d{18}\b/', function (array $m) use (&$flags, $ownNip): string {
                if ($ownNip !== '' && $m[0] === $ownNip) {
                    return $m[0];
                }
                $flags[] = 'foreign_nip';

                return '[NIP disensor]';
            }, $text) ?? $text;
        }

        $flags = array_values(array_unique($flags));

        return ['text' => $text, 'redacted' => $flags !== [], 'flags' => $flags];
    }

    /** Kategori pertanyaan (untuk statistik), di luar kategori penolakan. */
    public function classify(string $text, array $toolNames = []): string
    {
        $byTool = [
            'list_my_courses' => 'kursus_saya',
            'get_my_course_detail' => 'progres_belajar',
            'list_my_certificates' => 'sertifikat',
            'list_my_pending_tasks' => 'tugas_hari_ini',
            'list_my_quiz_results' => 'nilai_kuis',
            'get_my_profile' => 'profil',
            'search_course_catalog' => 'katalog',
            'get_mooc_help' => 'panduan_mooc',
        ];

        foreach ($toolNames as $tool) {
            if (isset($byTool[$tool])) {
                return $byTool[$tool];
            }
        }

        $t = $this->normalize($text);

        return match (true) {
            (bool) preg_match('/sertifikat|certificate/i', $t) => 'sertifikat',
            (bool) preg_match('/progres|progress|kemajuan|selesai/i', $t) => 'progres_belajar',
            (bool) preg_match('/tugas|deadline|tenggat|hari ini|kerjakan/i', $t) => 'tugas_hari_ini',
            (bool) preg_match('/nilai|skor|kuis|ujian|test/i', $t) => 'nilai_kuis',
            (bool) preg_match('/katalog|kursus baru|daftar kursus/i', $t) => 'katalog',
            (bool) preg_match('/kursus|course|materi|topik/i', $t) => 'kursus_saya',
            (bool) preg_match('/cara|bagaimana|fitur|menu|panduan/i', $t) => 'panduan_mooc',
            default => 'umum',
        };
    }

    private function normalize(string $text): string
    {
        // Samakan variasi penulisan sederhana (spasi ganda, zero-width, leetspeak dasar).
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /** @return array{allowed:bool, category:string, reason:string, flags:list<string>} */
    private function block(string $category, string $reason): array
    {
        return ['allowed' => false, 'category' => $category, 'reason' => $reason, 'flags' => [$category]];
    }
}
