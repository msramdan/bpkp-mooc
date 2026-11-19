<?php

/*
|--------------------------------------------------------------------------
| AI Assistant MOOC
|--------------------------------------------------------------------------
| Nilai di sini hanya default awal. Konfigurasi aktif disimpan di tabel
| ai_assistant_settings dan diubah lewat menu "AI Assistant Settings"
| (Super Admin), sehingga tidak perlu ubah kode / deploy ulang.
*/

return [

    'defaults' => [
        'enabled' => false,
        'provider' => 'anthropic',
        'provider_enabled' => true,
        'model' => 'claude-opus-5-5',
        'sumopod_model' => 'claude-opus-5-5',
        'effort' => 'low',
        'max_output_tokens' => 4096,
        'request_timeout' => 60,
        'custom_instructions' => '',

        'usage_limit_enabled' => true,
        'daily_limit' => 5,
        'reset_hour' => 0,
        'timezone' => 'Asia/Jakarta',

        'security_firewall_enabled' => true,
        'prompt_injection_protection_enabled' => true,
        'sensitive_data_protection_enabled' => true,
        'token_usage_tracking_enabled' => true,

        'role_access' => [],
        'log_retention_days' => 365,
        'history_window' => 10,
        'max_question_length' => 1000,
    ],

    /*
    | Provider AI. Base URL tidak bisa diubah dari dashboard agar API key tidak
    | bisa diarahkan ke host lain.
    | - anthropic: SDK resmi Anthropic (Messages API).
    | - sumopod  : gateway SumoPod, format OpenAI-compatible (/v1/chat/completions)
    |              sesuai dokumentasi https://sumopod.com/dashboard/ai/quickstart.
    */
    'providers' => [
        'anthropic' => [
            'label' => 'Anthropic Claude',
            'tagline' => 'Langsung ke Anthropic (SDK resmi)',
            'base_url' => null,
            'key_hint' => 'sk-ant-…',
            'console' => 'https://console.anthropic.com',
        ],
        'sumopod' => [
            'label' => 'SumoPod',
            'tagline' => 'AI gateway (OpenAI-compatible), bayar pakai saldo SumoPod',
            'base_url' => env('AI_ASSISTANT_SUMOPOD_BASE_URL', 'https://ai.sumopod.com'),
            'key_hint' => 'sk-…',
            'console' => 'https://sumopod.com/dashboard/ai/models',
        ],
    ],

    'effort_levels' => ['low', 'medium', 'high'],

    'timezones' => [
        'Asia/Jakarta' => 'WIB (Asia/Jakarta)',
        'Asia/Makassar' => 'WITA (Asia/Makassar)',
        'Asia/Jayapura' => 'WIT (Asia/Jayapura)',
    ],

    /*
    | Proteksi keamanan inti (firewall, prompt injection, data sensitif) hanya
    | bisa dimatikan dari dashboard bila server mengizinkan lewat env ini.
    | Isolasi data user & audit log percakapan selalu aktif (tidak bisa dimatikan).
    */
    'allow_security_override' => (bool) env('AI_ASSISTANT_ALLOW_SECURITY_OVERRIDE', false),

    /*
    | Provider tiruan untuk pengujian lokal tanpa API key. Hanya dipakai bila
    | APP_ENV=local/testing; diabaikan di production.
    */
    'fake_provider' => (bool) env('AI_ASSISTANT_FAKE_PROVIDER', false),

    /*
    | Estimasi biaya (USD per 1 juta token). Harga bisa berubah, cek
    | https://claude.com/pricing. Model yang tidak terdaftar -> biaya tidak dihitung.
    */
    'pricing' => [
        'claude-fable-5-2' => ['input' => 8.00, 'output' => 40.00, 'cache_read' => 0.20],
        'claude-opus-5-5' => ['input' => 4.00, 'output' => 20.00, 'cache_read' => 0.20],
        'claude-opus-5' => ['input' => 5.00, 'output' => 25.00, 'cache_read' => 0.50],
        'claude-opus-4-8' => ['input' => 5.00, 'output' => 25.00, 'cache_read' => 0.50],
        'claude-sonnet-5-5' => ['input' => 2.00, 'output' => 10.00, 'cache_read' => 0.10],
        'claude-sonnet-5' => ['input' => 2.00, 'output' => 10.00, 'cache_read' => 0.20],
        'claude-haiku-5-5' => ['input' => 0.10, 'output' => 0.50, 'cache_read' => 0.01],
        'claude-haiku-4-5' => ['input' => 1.00, 'output' => 5.00, 'cache_read' => 0.10],
    ],
];
