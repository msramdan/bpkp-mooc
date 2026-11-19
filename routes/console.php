<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mooc:recalculate-scores {--dry-run : Tampilkan perubahan tanpa menyimpan}', function () {
    $scoring = app(\App\Services\SurveyScoring::class);
    $changed = 0;
    $total = 0;

    \App\Models\SurveyResponse::query()->with(['survey.questions.options', 'answers'])->chunkById(200, function ($responses) use ($scoring, &$changed, &$total) {
        foreach ($responses as $response) {
            $total++;
            $before = \App\Services\SurveyScoring::percentage($response);

            if ($this->option('dry-run')) {
                \Illuminate\Support\Facades\DB::beginTransaction();
            }

            $scoring->recalculate($response);
            $after = \App\Services\SurveyScoring::percentage($response);

            if ($this->option('dry-run')) {
                \Illuminate\Support\Facades\DB::rollBack();
            }

            if ($before !== $after) {
                $changed++;
                $this->line("{$response->id}: {$before} -> {$after}");
            }
        }
    });

    $this->info(($this->option('dry-run') ? '[dry-run] ' : '')."{$changed} dari {$total} jawaban berubah nilainya.");
})->purpose('Hitung ulang skor kuis dengan aturan baru (hanya PG & checklist berkunci)');

Artisan::command('ai-assistant:prune {--dry-run : Hanya hitung tanpa menghapus}', function () {
    $settings = app(\App\Services\AiAssistant\AiAssistantSettings::class);
    $days = max(30, (int) $settings->get('log_retention_days', 365));
    $cutoff = now()->subDays($days);

    // Request macet ("processing") lebih dari 1 jam -> tandai gagal, kuota tidak terpotong.
    $stale = \App\Models\AiAssistantRequestLog::query()
        ->where('status', \App\Models\AiAssistantRequestLog::STATUS_PROCESSING)
        ->where('requested_at', '<', now()->subHour());

    $logs = \App\Models\AiAssistantRequestLog::query()->where('requested_at', '<', $cutoff);
    $audit = \App\Models\AiAssistantAuditLog::query()->where('created_at', '<', $cutoff);
    $usages = \App\Models\AiAssistantUsage::query()->where('usage_date', '<', $cutoff->toDateString());

    $this->info("Retensi {$days} hari (sebelum {$cutoff->toDateString()}):");
    $this->line(' - request log: '.(clone $logs)->count());
    $this->line(' - audit admin: '.(clone $audit)->count());
    $this->line(' - kuota harian: '.(clone $usages)->count());
    $this->line(' - request macet: '.(clone $stale)->count());

    if ($this->option('dry-run')) {
        $this->comment('[dry-run] tidak ada data yang dihapus.');

        return;
    }

    $stale->update(['status' => 'failed', 'error_code' => 'stale_request', 'error_message' => 'Request tidak selesai.']);
    $logs->delete();
    $audit->delete();
    $usages->delete();
    \App\Models\AiAssistantConversation::query()->where('last_message_at', '<', $cutoff)->delete();

    $this->info('Selesai.');
})->purpose('Hapus log AI Assistant yang melewati masa retensi');

\Illuminate\Support\Facades\Schedule::command('ai-assistant:prune')->dailyAt('02:30');
