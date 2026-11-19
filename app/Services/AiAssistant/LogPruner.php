<?php

namespace App\Services\AiAssistant;

use App\Models\AiAssistantAuditLog;
use App\Models\AiAssistantConversation;
use App\Models\AiAssistantRequestLog;
use App\Models\AiAssistantUsage;
use Illuminate\Support\Carbon;

/**
 * Pembersihan data AI Assistant yang melewati masa retensi.
 * Dijalankan manual oleh Super Admin (tombol di Settings) atau `php artisan ai-assistant:prune`.
 * Tidak memakai cron.
 */
class LogPruner
{
    public function __construct(private readonly AiAssistantSettings $settings) {}

    public function retentionDays(): int
    {
        return max(30, (int) $this->settings->get('log_retention_days', 365));
    }

    public function cutoff(): Carbon
    {
        return now()->subDays($this->retentionDays());
    }

    /** @return array{request_logs:int, conversations:int, audit_logs:int, usages:int, cutoff:string, days:int} */
    public function preview(): array
    {
        $cutoff = $this->cutoff();

        return [
            'request_logs' => AiAssistantRequestLog::query()->where('requested_at', '<', $cutoff)->count(),
            'conversations' => AiAssistantConversation::query()->where('last_message_at', '<', $cutoff)->count(),
            'audit_logs' => AiAssistantAuditLog::query()->where('created_at', '<', $cutoff)->count(),
            'usages' => AiAssistantUsage::query()->where('usage_date', '<', $cutoff->toDateString())->count(),
            'cutoff' => $cutoff->toDateString(),
            'days' => $this->retentionDays(),
        ];
    }

    /** Hapus data lama. Mengembalikan jumlah baris yang dihapus per jenis. */
    public function prune(): array
    {
        $cutoff = $this->cutoff();

        // Request macet (>1 jam "processing") ditandai gagal; kuota tidak terpotong.
        AiAssistantRequestLog::query()
            ->where('status', AiAssistantRequestLog::STATUS_PROCESSING)
            ->where('requested_at', '<', now()->subHour())
            ->update(['status' => AiAssistantRequestLog::STATUS_FAILED, 'error_code' => 'stale_request', 'error_message' => 'Request tidak selesai.']);

        return [
            'conversations' => AiAssistantConversation::query()->where('last_message_at', '<', $cutoff)->delete(),
            'request_logs' => AiAssistantRequestLog::query()->where('requested_at', '<', $cutoff)->delete(),
            'audit_logs' => AiAssistantAuditLog::query()->where('created_at', '<', $cutoff)->delete(),
            'usages' => AiAssistantUsage::query()->where('usage_date', '<', $cutoff->toDateString())->delete(),
            'cutoff' => $cutoff->toDateString(),
            'days' => $this->retentionDays(),
        ];
    }
}
