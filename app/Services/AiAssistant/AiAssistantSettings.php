<?php

namespace App\Services\AiAssistant;

use App\Models\AiAssistantSetting;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Konfigurasi AI Assistant yang tersimpan di database (bisa diubah lewat dashboard).
 *
 * - Dibaca langsung dari DB sekali per request (tanpa cache lintas proses) agar
 *   perubahan / pergantian provider langsung berlaku di semua worker tanpa data basi.
 * - API key per provider disimpan terenkripsi dan hanya dibaca oleh provider di backend.
 * - Setiap provider punya API key, model, status koneksi, dan daftar model sendiri,
 *   sehingga berpindah provider tidak menghapus konfigurasi provider lain.
 */
class AiAssistantSettings
{
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

        $stored = [];
        if (Schema::hasTable('ai_assistant_settings')) {
            $stored = AiAssistantSetting::query()
                ->where('is_encrypted', false)
                ->get(['key', 'value'])
                ->mapWithKeys(fn (AiAssistantSetting $s) => [$s->key => json_decode((string) $s->value, true)])
                ->all();
        }

        $settings = array_replace(config('ai_assistant.defaults'), $stored);

        if (! array_key_exists($settings['provider'] ?? '', $this->providers())) {
            $settings['provider'] = 'anthropic';
        }

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

    /** Simpan beberapa nilai sekaligus secara atomik (mis. provider + model saat switch). */
    public function set(array $values, ?int $userId = null): void
    {
        DB::transaction(function () use ($values, $userId): void {
            foreach ($values as $key => $value) {
                if ($this->isSecretKey($key)) {
                    continue;
                }

                AiAssistantSetting::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => json_encode($value), 'is_encrypted' => false, 'updated_by' => $userId],
                );
            }
        });

        $this->flush();
    }

    /* ---------------- Provider ---------------- */

    /** @return array<string, array{label:string, base_url:?string, docs:?string}> */
    public function providers(): array
    {
        return config('ai_assistant.providers');
    }

    public function activeProvider(): string
    {
        return (string) $this->get('provider', 'anthropic');
    }

    public function modelFor(?string $provider = null): string
    {
        $provider ??= $this->activeProvider();
        $key = $this->modelKey($provider);

        return (string) ($this->get($key) ?: config("ai_assistant.defaults.{$key}", ''));
    }

    public function modelKey(string $provider): string
    {
        return $provider === 'anthropic' ? 'model' : "{$provider}_model";
    }

    /** @return list<array{id:string, name:string}> */
    public function availableModels(string $provider): array
    {
        return (array) $this->get("available_models_{$provider}", []);
    }

    public function connectionStatus(string $provider): array
    {
        return (array) $this->get("connection_status_{$provider}", []);
    }

    /* ---------------- API key (terenkripsi, per provider) ---------------- */

    public function setApiKey(string $apiKey, ?int $userId = null, ?string $provider = null): void
    {
        $provider ??= $this->activeProvider();

        AiAssistantSetting::query()->updateOrCreate(
            ['key' => $this->secretKey($provider)],
            ['value' => Crypt::encryptString(trim($apiKey)), 'is_encrypted' => true, 'updated_by' => $userId],
        );

        $this->flush();
    }

    public function removeApiKey(?string $provider = null): void
    {
        AiAssistantSetting::query()->where('key', $this->secretKey($provider ?? $this->activeProvider()))->delete();
        $this->flush();
    }

    /** Hanya untuk dipakai provider di backend. Jangan dikirim ke view / log. */
    public function apiKey(?string $provider = null): ?string
    {
        $row = AiAssistantSetting::query()->where('key', $this->secretKey($provider ?? $this->activeProvider()))->first();

        if (! $row || $row->value === null || $row->value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($row->value);
        } catch (DecryptException) {
            return null;
        }
    }

    public function hasApiKey(?string $provider = null): bool
    {
        return AiAssistantSetting::query()
            ->where('key', $this->secretKey($provider ?? $this->activeProvider()))
            ->whereNotNull('value')
            ->exists();
    }

    public function apiKeyUpdatedAt(?string $provider = null): ?CarbonImmutable
    {
        $at = AiAssistantSetting::query()->where('key', $this->secretKey($provider ?? $this->activeProvider()))->value('updated_at');

        return $at ? CarbonImmutable::parse($at) : null;
    }

    /* ---------------- Lain-lain ---------------- */

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
        $this->loaded = null;
    }

    private function secretKey(string $provider): string
    {
        // Kompatibel dengan data lama: key Anthropic tetap bernama "api_key".
        return $provider === 'anthropic' ? 'api_key' : "{$provider}_api_key";
    }

    private function isSecretKey(string $key): bool
    {
        return $key === 'api_key' || str_ends_with($key, '_api_key');
    }
}
