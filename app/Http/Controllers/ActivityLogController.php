<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Support\Activity\ActivityDescriber;
use App\Support\Roles;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Log aktivitas seluruh user (Super Admin). Membuka & export log juga tercatat
 * otomatis oleh middleware LogRequestActivity.
 */
class ActivityLogController extends Controller implements HasMiddleware
{
    public const TZ = 'Asia/Jakarta';

    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Roles::SUPER_ADMIN),
            new Middleware('permission:activity log view'),
            new Middleware('permission:activity log export', only: ['export']),
            new Middleware('permission:activity log manage', only: ['prune']),
        ];
    }

    public function index(Request $request): View
    {
        // simplePaginate: tanpa COUNT(*) atas jutaan baris; urut id memakai primary key/index.
        $logs = $this->filtered($request)
            ->orderByDesc('id')
            ->simplePaginate(50)
            ->withQueryString();

        // Statistik hari ini di-cache 60 detik (tidak dihitung ulang tiap admin membuka halaman).
        $stats = \Illuminate\Support\Facades\Cache::remember('activity-log:stats:'.now(self::TZ)->toDateString(), 60, function () {
            $todayStart = CarbonImmutable::now(self::TZ)->startOfDay()->utc();

            return (object) ActivityLog::query()
                ->createdBetween($todayStart, null)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('COUNT(DISTINCT CASE WHEN causer_id IS NOT NULL THEN causer_id END) as users')
                ->selectRaw("SUM(log_name = 'data') as data_changes")
                ->selectRaw("SUM(log_name = 'auth' AND event = 'failed') as failed_logins")
                ->selectRaw('SUM(status_code >= 500) as server_errors')
                ->first()
                ?->getAttributes();
        });

        $subjectUser = $request->filled('user_id') ? User::query()->find((int) $request->query('user_id')) : null;

        return view('activity-logs.index', [
            'logs' => $logs,
            'stats' => $stats,
            'subjectUser' => $subjectUser,
            'logNames' => ActivityLog::LOG_NAMES,
            'models' => ActivityDescriber::modelOptions(),
            'events' => ['request', 'created', 'updated', 'deleted', 'login', 'logout', 'failed', 'lockout'],
        ]);
    }

    public function show(ActivityLog $activityLog): View
    {
        $related = $activityLog->request_id
            ? ActivityLog::query()->where('request_id', $activityLog->request_id)->whereKeyNot($activityLog->id)->orderBy('id')->limit(100)->get()
            : collect();

        return view('activity-logs.show', ['log' => $activityLog, 'related' => $related, 'admin' => true]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->orderBy('id');
        $filename = 'log-aktivitas-'.now(self::TZ)->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Waktu (WIB)', 'Jenis', 'Event', 'Aktivitas', 'User ID', 'Nama', 'Email', 'Role', 'IP', 'Perangkat', 'Method', 'Route', 'URL', 'Status', 'Durasi (ms)', 'Data', 'Data ID', 'Sebelum', 'Sesudah', 'Request ID'], ';');

            $query->chunkById(1000, function ($logs) use ($out): void {
                foreach ($logs as $l) {
                    fputcsv($out, [
                        $l->id,
                        $l->created_at?->setTimezone(self::TZ)->format('Y-m-d H:i:s'),
                        ActivityLog::LOG_NAMES[$l->log_name] ?? $l->log_name,
                        ActivityDescriber::eventLabel($l->event),
                        $this->csvSafe($l->description),
                        $l->causer_id,
                        $this->csvSafe((string) $l->causer_name),
                        $l->causer_email,
                        $l->causer_role,
                        $l->ip_address,
                        $this->csvSafe((string) $l->user_agent),
                        $l->method,
                        $l->route_name,
                        $this->csvSafe((string) $l->url),
                        $l->status_code,
                        $l->duration_ms,
                        $l->subject_type ? $l->subjectLabel() : '',
                        $l->subject_id,
                        $l->before() ? $this->csvSafe(json_encode($l->before(), JSON_UNESCAPED_UNICODE)) : '',
                        $l->after() ? $this->csvSafe(json_encode($l->after(), JSON_UNESCAPED_UNICODE)) : '',
                        $l->request_id,
                    ], ';');
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Pembersihan manual log lama (tanpa cron). */
    public function prune(Request $request): RedirectResponse
    {
        $request->validate(['confirm' => ['accepted']], ['confirm.accepted' => __('Centang konfirmasi untuk menghapus log lama.')]);

        $days = max(30, (int) config('activitylog.clean_after_days', 90));
        $cutoff = now()->subDays($days);
        $deleted = 0;

        // Hapus per batch 5.000 baris lewat rentang primary key: cepat & tidak mengunci tabel lama.
        $ceil = ActivityLog::query()->where('created_at', '<', $cutoff)->orderByDesc('created_at')->orderByDesc('id')->value('id');
        if ($ceil !== null) {
            do {
                $n = ActivityLog::query()->where('id', '<=', $ceil)->where('created_at', '<', $cutoff)->orderBy('id')->limit(5000)->delete();
                $deleted += $n;
            } while ($n > 0);
        }

        activity('data')->event('deleted')->withProperties(['retensi_hari' => $days, 'sebelum' => $cutoff->toDateString(), 'jumlah' => $deleted])
            ->log("Membersihkan {$deleted} log aktivitas lebih lama dari {$days} hari");

        return back()->with('success', __(':n log aktivitas lama (sebelum :date) dihapus.', ['n' => number_format($deleted, 0, ',', '.'), 'date' => $cutoff->toDateString()]));
    }

    private function filtered(Request $request): Builder
    {
        $q = ActivityLog::query();

        if ($request->filled('user_id')) {
            $q->where('causer_type', (new User)->getMorphClass())->where('causer_id', (int) $request->query('user_id'));
        }

        if ($request->filled('user')) {
            $term = '%'.$this->like((string) $request->query('user')).'%';
            $q->where(fn ($w) => $w->where('causer_name', 'like', $term)->orWhere('causer_email', 'like', $term));
        }

        if ($request->filled('q')) {
            $term = '%'.$this->like((string) $request->query('q')).'%';
            $q->where(fn ($w) => $w->where('description', 'like', $term)->orWhere('url', 'like', $term)->orWhere('route_name', 'like', $term));
        }

        if (array_key_exists((string) $request->query('log'), ActivityLog::LOG_NAMES)) {
            $q->where('log_name', $request->query('log'));
        }

        if ($request->filled('event')) {
            $q->where('event', mb_substr((string) $request->query('event'), 0, 40));
        }

        if (array_key_exists((string) $request->query('subject_type'), ActivityDescriber::modelOptions())) {
            $q->where('subject_type', $request->query('subject_type'));
        }

        if ($request->filled('subject_id')) {
            $q->where('subject_id', mb_substr((string) $request->query('subject_id'), 0, 64));
        }

        if (in_array($request->query('method'), ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $q->where('method', $request->query('method'));
        }

        match ($request->query('status')) {
            'ok' => $q->whereBetween('status_code', [200, 399]),
            '4xx' => $q->whereBetween('status_code', [400, 499]),
            '5xx' => $q->where('status_code', '>=', 500),
            default => null,
        };

        if ($request->filled('ip')) {
            $q->where('ip_address', mb_substr((string) $request->query('ip'), 0, 45));
        }

        if ($request->filled('request_id') && preg_match('/^[0-9a-f-]{36}$/i', (string) $request->query('request_id'))) {
            $q->where('request_id', $request->query('request_id'));
        }

        $q->createdBetween(
            $this->validDate($request->query('from')) ? CarbonImmutable::parse($request->query('from'), self::TZ)->startOfDay()->utc() : null,
            $this->validDate($request->query('to')) ? CarbonImmutable::parse($request->query('to'), self::TZ)->endOfDay()->utc() : null,
        );

        return $q;
    }

    private function validDate(mixed $v): bool
    {
        return is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) !== false;
    }

    private function like(string $v): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], mb_substr($v, 0, 100));
    }

    private function csvSafe(string $v): string
    {
        return preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v;
    }
}
