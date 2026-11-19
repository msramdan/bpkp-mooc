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
    // Manual (tanpa cron). Super Admin juga bisa menjalankan dari menu AI Assistant Settings.
    $pruner = app(\App\Services\AiAssistant\LogPruner::class);
    $result = $this->option('dry-run') ? $pruner->preview() : $pruner->prune();

    $this->info("Retensi {$result['days']} hari (data sebelum {$result['cutoff']}):");
    $this->line(' - log request  : '.$result['request_logs']);
    $this->line(' - percakapan   : '.$result['conversations']);
    $this->line(' - audit admin  : '.$result['audit_logs']);
    $this->line(' - kuota harian : '.$result['usages']);
    $this->comment($this->option('dry-run') ? '[dry-run] tidak ada data yang dihapus.' : 'Selesai dihapus.');
})->purpose('Hapus data AI Assistant yang melewati masa retensi (manual)');
