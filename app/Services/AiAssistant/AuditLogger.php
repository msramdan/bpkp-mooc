<?php

namespace App\Services\AiAssistant;

use App\Models\AiAssistantAuditLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Catat aktivitas Super Admin pada fitur AI Assistant (membuka audit, export,
 * mengubah konfigurasi). Metadata tidak boleh berisi API key / secret.
 */
class AuditLogger
{
    private const FORBIDDEN_KEY_PATTERN = '/^(api_key|password|secret|token|access_token|auth_token)$|(_password|_secret|_api_key|_access_token)$/';

    public function log(Request $request, string $action, ?string $description = null, ?string $targetType = null, ?string $targetId = null, array $metadata = []): void
    {
        /** @var User|null $actor */
        $actor = $request->user();

        AiAssistantAuditLog::query()->create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
            'actor_email' => $actor?->email,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'description' => $description ? mb_substr($description, 0, 500) : null,
            'metadata' => $this->scrub($metadata) ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);
    }

    private function scrub(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && preg_match(self::FORBIDDEN_KEY_PATTERN, strtolower($key))) {
                $data[$key] = '[redacted]';

                continue;
            }

            if (is_string($value)) {
                $data[$key] = preg_replace('/sk-ant-[A-Za-z0-9_\-]+/', '[redacted]', $value);
            } elseif (is_array($value)) {
                $data[$key] = $this->scrub($value);
            }
        }

        return $data;
    }
}
