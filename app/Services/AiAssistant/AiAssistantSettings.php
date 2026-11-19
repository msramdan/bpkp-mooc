<?php

namespace App\Services\AiAssistant;

use App\Models\AiAssistantSetting;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

/**
 * Konfigurasi AI Assistant yang tersimpan di database (bisa diubah lewat dashboard).
 * API key disimpan terenkripsi, tidak ikut cache, dan hanya dibaca oleh provider.
 */
class AiAssistantSettings
{
    private const CACHE_KEY = 'ai_assistant.settings.v1';

    private const SECRET_KEYS = ['api_key'];

    /** Proteksi yang selalu aktif dan tidak tersedia sebagai pengaturan. */
    public const ALWAYS_ON = ['user_data_isolation', 'conversation_audit_log'];

    /** Proteksi inti: hanya bisa dimatikan jika server mengizinkan (AI_ASSISTANT_ALLOW_SECURITY_OVERRIDE). */
    public const CORE_SECURITY_KEYS = [
        'security_firewall_enabled',
        'prompt_injection_protection_enabled',
        'sensitive_data_protection_enabled',
    ];

    private ?array $loaded = null;

    /** @return array<string, mixed> */
    public function all(): array
    {
        if ($this->loaded !== null) {
            return $this->loaded;
        }

        $stored = Cache::remember(self::CACHE_KEY, 300, function (): array {
            if (! Schema::hasTable('ai_assistant_settings')) {
                return [];
            }

            return AiAssistantSetting::query()
                ->whereNotIn('key', self::SECRET_KEYS)
                ->get(['key', 'value'])
                ->mapWithKeys(fn (AiAssistantSetting $s) => [$s->key => json_decode((string) $s->value, true)])
                ->all();
        });

        $settings = array_replace(config('ai_assistant.defaults'), $stored);

        if (! config('ai_assistant.allow_security_override')) {
            foreach (self::CORE_SECURITY_KEYS as $key) {
                $settings[$key] = true;
            }
        }

        return $this->loaded = $settings;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function bool(string $key): bool
    {
        return (bool) $this->get($key, false);
    }

    public function int(string $key): int
    {
        return (int) $this->get($key, 0);
    }

    /** @param  array<string, mixed>  $values */
    public function set(array $values, ?int $userId = null): void
    {
        foreach ($values as $key => $value) {
            if (in_array($key, self::SECRET_KEYS, true)) {
                continue;
            }

            AiAssistantSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => json_encode($value), 'is_encrypted' => false, 'updated_by' => $userId],
            );
        }

        $this->flush();
    }

    public function setApiKey(string $apiKey, ?int $userId = null): void
    {
        AiAssistantSetting::query()->updateOrCreate(
            ['key' => 'api_key'],
            ['value' => Crypt::encryptString(trim($apiKey)), 'is_encrypted' => true, 'updated_by' => $userId],
        );

        $this->flush();
    }

    public function removeApiKey(): void
    {
        AiAssistantSetting::query()->where('key', 'api_key')->delete();
        $this->flush();
    }

    /** Hanya untuk dipakai provider di backend. Jangan dikirim ke view / log. */
    public function apiKey(): ?string
    {
        $row = AiAssistantSetting::query()->where('key', 'api_key')->first();

        if (! $row || $row->value === null || $row->value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($row->value);
        } catch (DecryptException) {
            return null;
        }
    }

    public function hasApiKey(): bool
    {
        return AiAssistantSetting::query()->where('key', 'api_key')->whereNotNull('value')->exists();
    }

    public function apiKeyUpdatedAt(): ?CarbonImmutable
    {
        $at = AiAssistantSetting::query()->where('key', 'api_key')->value('updated_at');

        return $at ? CarbonImmutable::parse($at) : null;
    }

    public function timezone(): string
    {
        $tz = (string) $this->get('timezone', 'Asia/Jakarta');

        return array_key_exists($tz, config('ai_assistant.timezones')) ? $tz : 'Asia/Jakarta';
    }

    public function roleAllowed(?string $role): bool
    {
        if ($role === null) {
            return false;
        }

        $map = (array) $this->get('role_access', []);

        // Default: aktif untuk semua role selama fitur global aktif.
        return (bool) ($map[$role] ?? true);
    }

    public function securityOverrideAllowed(): bool
    {
        return (bool) config('ai_assistant.allow_security_override');
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->loaded = null;
    }
}
