<?php

namespace App\Http\Controllers\AiAssistant;

use App\Http\Controllers\Controller;
use App\Models\AiAssistantAuditLog;
use App\Models\AiAssistantRequestLog;
use App\Models\User;
use App\Services\AiAssistant\AuditLogger;
use App\Services\AiAssistant\QuotaManager;
use App\Support\Roles;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Monitoring & audit AI Assistant. Hanya Super Admin (role + permission dicek
 * di route dan di sini). Setiap pembukaan riwayat & export dicatat ke audit log.
 */
class MonitoringController extends Controller implements HasMiddleware
{
    public const USER_STATUSES = [
        'active_today' => 'Aktif hari ini',
        'quota_exhausted' => 'Kuota habis hari ini',
        'idle_today' => 'Tidak aktif hari ini',
        'has_rejected' => 'Pernah ditolak firewall',
    ];

    public const SORTS = ['name', 'today', 'month', 'period', 'total', 'last_used'];

    public function __construct(
        private readonly QuotaManager $quota,
        private readonly AuditLogger $audit,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Roles::SUPER_ADMIN),
            new Middleware('permission:ai assistant monitor view'),
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  A. Dashboard statistik                                              */
    /* ------------------------------------------------------------------ */
    public function dashboard(Request $request): View
    {
        [$from, $to, $period] = $this->period($request);
        $today = $this->quota->usageDate();

        $inRange = fn () => AiAssistantRequestLog::query()->whereBetween('usage_date', [$from, $to]);

        $stats = $inRange()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(status = 'success') as success")
            ->selectRaw("SUM(status = 'failed') as failed")
            ->selectRaw("SUM(status = 'rejected' AND security_status = 'blocked') as blocked")
            ->selectRaw("SUM(status = 'rejected' AND category = 'quota_exceeded') as quota_rejected")
            ->selectRaw('SUM(quota_counted) as quota_used')
            ->selectRaw('COUNT(DISTINCT user_id) as users')
            ->selectRaw('COUNT(DISTINCT conversation_id) as conversations')
            ->selectRaw('COALESCE(SUM(input_tokens),0) as input_tokens')
            ->selectRaw('COALESCE(SUM(output_tokens),0) as output_tokens')
            ->selectRaw('COALESCE(SUM(cost_usd),0) as cost')
            ->selectRaw("AVG(CASE WHEN status = 'success' THEN duration_ms END) as avg_ms")
            ->first();

        $todayStats = AiAssistantRequestLog::query()
            ->whereDate('usage_date', $today)
            ->selectRaw('COUNT(*) as total, COUNT(DISTINCT user_id) as users')
            ->first();

        $topUsers = $inRange()
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('MAX(user_name) as user_name'), DB::raw('MAX(user_email) as user_email'))
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(status = 'success') as success")
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $daily = $inRange()
            ->select('usage_date')
            ->selectRaw("SUM(status = 'success') as success")
            ->selectRaw("SUM(status = 'failed') as failed")
            ->selectRaw("SUM(status = 'rejected') as rejected")
            ->groupBy('usage_date')
            ->orderBy('usage_date')
            ->get()
            ->keyBy(fn ($r) => CarbonImmutable::parse($r->usage_date)->toDateString());

        // Lengkapi hari kosong agar grafik kontinu (maks 92 hari).
        $series = ['labels' => [], 'success' => [], 'failed' => [], 'rejected' => []];
        $cursor = CarbonImmutable::parse($from);
        $end = CarbonImmutable::parse($to);
        $guard = 0;
        while ($cursor->lessThanOrEqualTo($end) && $guard++ < 92) {
            $row = $daily->get($cursor->toDateString());
            $series['labels'][] = $cursor->translatedFormat('d M');
            $series['success'][] = (int) ($row->success ?? 0);
            $series['failed'][] = (int) ($row->failed ?? 0);
            $series['rejected'][] = (int) ($row->rejected ?? 0);
            $cursor = $cursor->addDay();
        }

        $categories = $inRange()
            ->whereNotNull('category')
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $this->audit->log($request, 'monitor.dashboard_viewed', 'Membuka dashboard monitoring AI', metadata: ['from' => $from, 'to' => $to]);

        return view('ai-assistant.admin.dashboard', compact('stats', 'todayStats', 'topUsers', 'series', 'categories', 'from', 'to', 'period'));
    }

    /* ------------------------------------------------------------------ */
    /*  B. Aktivitas per user                                               */
    /* ------------------------------------------------------------------ */
    public function users(Request $request): View
    {
        [$from, $to, $period] = $this->period($request, 'month');
        $query = $this->usersQuery($request, $from, $to);
        $users = $query->paginate(15)->withQueryString();

        $this->audit->log($request, 'monitor.users_viewed', 'Membuka daftar aktivitas AI per user', metadata: $request->only(['q', 'status', 'from', 'to']));

        return view('ai-assistant.admin.users', [
            'users' => $users,
            'from' => $from,
            'to' => $to,
            'period' => $period,
            'limit' => $this->quota->limit(),
            'limitEnabled' => $this->quota->limitEnabled(),
            'statuses' => self::USER_STATUSES,
            'sort' => $this->sort($request),
            'dir' => $request->query('dir') === 'asc' ? 'asc' : 'desc',
        ]);
    }

    public function exportUsers(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request, 'month');
        $rows = $this->usersQuery($request, $from, $to)->limit(10000)->get();
        $limit = $this->quota->limit();

        $this->audit->log($request, 'monitor.users_exported', 'Export aktivitas AI per user', metadata: ['rows' => $rows->count(), 'from' => $from, 'to' => $to] + $request->only(['q', 'status']));

        return $this->csv('aktivitas-ai-per-user-'.now()->format('Ymd-His').'.csv', [
            'No', 'Nama', 'Email', 'Pertanyaan Hari Ini', 'Pertanyaan Bulan Ini', "Pertanyaan {$from} s/d {$to}",
            'Sisa Kuota Hari Ini', 'Total Pertanyaan', 'Ditolak Firewall', 'Terakhir Menggunakan AI', 'Status',
        ], function ($out) use ($rows, $limit): void {
            foreach ($rows as $i => $u) {
                fputcsv($out, [
                    $i + 1, $u->name, $u->email, (int) $u->today_count, (int) $u->month_count, (int) $u->period_count,
                    $this->quota->limitEnabled() ? max(0, $limit - (int) $u->today_success) : 'Tidak dibatasi',
                    (int) $u->total_count, (int) $u->blocked_count,
                    $u->last_used_at ? CarbonImmutable::parse($u->last_used_at)->setTimezone($this->tz())->format('Y-m-d H:i') : '-',
                    $this->userStatusLabel($u, $limit),
                ], ';');
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /*  C. Detail riwayat chat per user                                     */
    /* ------------------------------------------------------------------ */
    public function userDetail(Request $request, User $user): View
    {
        $logs = $this->logsQuery($request)
            ->where('user_id', $user->id)
            ->orderBy($request->query('order') === 'desc' ? 'requested_at' : 'requested_at', $request->query('order') === 'desc' ? 'desc' : 'asc')
            ->paginate(20)
            ->withQueryString();

        $summary = AiAssistantRequestLog::query()
            ->where('user_id', $user->id)
            ->selectRaw("COUNT(*) as total, SUM(status = 'success') as success, SUM(status = 'failed') as failed, SUM(status = 'rejected') as rejected, COALESCE(SUM(input_tokens + output_tokens),0) as tokens, COALESCE(SUM(cost_usd),0) as cost, MAX(requested_at) as last_used_at")
            ->first();

        $quota = $this->quota->status($user);

        $this->audit->log($request, 'monitor.user_history_viewed', "Membuka riwayat AI user {$user->email}", 'user', (string) $user->id, $request->only(['q', 'status', 'from', 'to']));

        return view('ai-assistant.admin.user-detail', [
            'subject' => $user,
            'logs' => $logs,
            'summary' => $summary,
            'quota' => $quota,
            'statuses' => AiAssistantRequestLog::STATUSES,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Log request (termasuk penolakan firewall)                           */
    /* ------------------------------------------------------------------ */
    public function requests(Request $request): View
    {
        $logs = $this->logsQuery($request)
            ->when($request->filled('user'), function (Builder $q) use ($request): void {
                $term = '%'.$this->escapeLike((string) $request->query('user')).'%';
                $q->where(fn ($w) => $w->where('user_name', 'like', $term)->orWhere('user_email', 'like', $term));
            })
            ->orderByDesc('requested_at')
            ->paginate(25)
            ->withQueryString();

        $categories = AiAssistantRequestLog::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        $this->audit->log($request, 'monitor.requests_viewed', 'Membuka log request AI', metadata: $request->only(['q', 'user', 'status', 'security', 'category', 'from', 'to']));

        return view('ai-assistant.admin.requests', [
            'logs' => $logs,
            'statuses' => AiAssistantRequestLog::STATUSES,
            'categories' => $categories,
        ]);
    }

    public function showRequest(Request $request, string $log): View
    {
        $model = AiAssistantRequestLog::query()->findOrFail($log);

        $this->audit->log($request, 'monitor.request_viewed', 'Membuka detail log request AI', 'request_log', $model->id);

        return view('ai-assistant.admin.request-show', ['log' => $model]);
    }

    public function exportRequests(Request $request): StreamedResponse
    {
        $query = $this->logsQuery($request)
            ->when($request->filled('user_id'), fn (Builder $q) => $q->where('user_id', (int) $request->query('user_id')))
            ->when($request->filled('user'), function (Builder $q) use ($request): void {
                $term = '%'.$this->escapeLike((string) $request->query('user')).'%';
                $q->where(fn ($w) => $w->where('user_name', 'like', $term)->orWhere('user_email', 'like', $term));
            })
            ->orderBy('requested_at');

        $count = (clone $query)->count();

        $this->audit->log($request, 'monitor.requests_exported', 'Export log request AI', metadata: ['rows' => $count] + $request->only(['q', 'user', 'user_id', 'status', 'security', 'category', 'from', 'to']));

        return $this->csv('log-ai-assistant-'.now()->format('Ymd-His').'.csv', [
            'ID Log', 'User ID', 'Nama', 'Email', 'Role', 'Conversation ID', 'Waktu Pertanyaan', 'Waktu Respons', 'Durasi (ms)',
            'Status', 'Status Keamanan', 'Alasan Penolakan', 'Kode Error', 'Kategori', 'Kategori Data', 'Provider', 'Model',
            'Token Input', 'Token Output', 'Estimasi Biaya (USD)', 'Kuota Terpakai', 'Limit', 'Pertanyaan', 'Jawaban',
        ], function ($out) use ($query): void {
            $query->chunk(500, function ($logs) use ($out): void {
                foreach ($logs as $l) {
                    fputcsv($out, [
                        $l->id, $l->user_id, $l->user_name, $l->user_email, $l->user_role, $l->conversation_id,
                        $this->fmt($l->requested_at), $this->fmt($l->responded_at), $l->duration_ms,
                        $l->status, $l->security_status, $l->rejection_reason, $l->error_code, $l->category,
                        implode(', ', $l->data_categories ?? []), $l->provider, $l->model,
                        $l->input_tokens, $l->output_tokens, $l->cost_usd,
                        $l->quota_counted ? $l->quota_used : '-', $l->quota_limit,
                        $this->csvSafe($l->question), $this->csvSafe((string) $l->answer),
                    ], ';');
                }
            });
        });
    }

    public function audit(Request $request): View
    {
        $logs = AiAssistantAuditLog::query()
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->query('action')))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$this->escapeLike((string) $request->query('q')).'%';
                $q->where(fn ($w) => $w->where('actor_name', 'like', $term)->orWhere('actor_email', 'like', $term)->orWhere('description', 'like', $term));
            })
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $actions = AiAssistantAuditLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('ai-assistant.admin.audit', compact('logs', 'actions'));
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                             */
    /* ------------------------------------------------------------------ */
    private function usersQuery(Request $request, string $from, string $to): Builder
    {
        $today = $this->quota->usageDate();
        $monthStart = CarbonImmutable::parse($today)->startOfMonth()->toDateString();

        $agg = AiAssistantRequestLog::query()
            ->whereNotNull('user_id')
            ->select('user_id')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(usage_date = ?) as today_count', [$today])
            ->selectRaw("SUM(usage_date = ? AND status = 'success') as today_success", [$today])
            ->selectRaw('SUM(usage_date >= ?) as month_count', [$monthStart])
            ->selectRaw('SUM(usage_date BETWEEN ? AND ?) as period_count', [$from, $to])
            ->selectRaw("SUM(security_status = 'blocked') as blocked_count")
            ->selectRaw('MAX(requested_at) as last_used_at')
            ->groupBy('user_id');

        $limit = $this->quota->limit();
        $sort = $this->sort($request);
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $query = User::query()
            ->joinSub($agg, 'ai', 'ai.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'ai.*')
            ->when($request->filled('q'), function (Builder $q) use ($request): void {
                $term = '%'.$this->escapeLike((string) $request->query('q')).'%';
                $q->where(fn ($w) => $w->where('users.name', 'like', $term)->orWhere('users.email', 'like', $term)->orWhere('users.nip', 'like', $term));
            })
            ->when($request->filled('from') || $request->filled('to') || $request->filled('period'), fn (Builder $q) => $q->where('ai.period_count', '>', 0));

        match ($request->query('status')) {
            'active_today' => $query->where('ai.today_count', '>', 0),
            'quota_exhausted' => $this->quota->limitEnabled() ? $query->where('ai.today_success', '>=', $limit) : $query->whereRaw('1 = 0'),
            'idle_today' => $query->where('ai.today_count', 0),
            'has_rejected' => $query->where('ai.blocked_count', '>', 0),
            default => null,
        };

        $column = match ($sort) {
            'name' => 'users.name',
            'today' => 'ai.today_count',
            'month' => 'ai.month_count',
            'period' => 'ai.period_count',
            'total' => 'ai.total_count',
            default => 'ai.last_used_at',
        };

        return $query->orderBy($column, $dir)->orderBy('users.id');
    }

    private function logsQuery(Request $request): Builder
    {
        $q = AiAssistantRequestLog::query();

        if ($request->filled('q')) {
            $term = '%'.$this->escapeLike((string) $request->query('q')).'%';
            $q->where(fn ($w) => $w->where('question', 'like', $term)->orWhere('answer', 'like', $term));
        }

        if (in_array($request->query('status'), AiAssistantRequestLog::STATUSES, true)) {
            $q->where('status', $request->query('status'));
        }

        if (in_array($request->query('security'), ['passed', 'blocked', 'redacted'], true)) {
            $q->where('security_status', $request->query('security'));
        }

        if ($request->filled('category')) {
            $q->where('category', (string) $request->query('category'));
        }

        if ($request->filled('from') && $this->validDate($request->query('from'))) {
            $q->where('usage_date', '>=', $request->query('from'));
        }

        if ($request->filled('to') && $this->validDate($request->query('to'))) {
            $q->where('usage_date', '<=', $request->query('to'));
        }

        return $q;
    }

    /** @return array{0:string, 1:string, 2:string} */
    private function period(Request $request, string $default = '7d'): array
    {
        $today = CarbonImmutable::parse($this->quota->usageDate());
        $period = (string) $request->query('period', $default);

        if ($this->validDate($request->query('from')) || $this->validDate($request->query('to'))) {
            $from = $this->validDate($request->query('from')) ? CarbonImmutable::parse($request->query('from')) : $today->subDays(6);
            $to = $this->validDate($request->query('to')) ? CarbonImmutable::parse($request->query('to')) : $today;
            if ($from->greaterThan($to)) {
                [$from, $to] = [$to, $from];
            }

            return [$from->toDateString(), $to->toDateString(), 'custom'];
        }

        [$from, $to] = match ($period) {
            'today' => [$today, $today],
            '30d' => [$today->subDays(29), $today],
            'month' => [$today->startOfMonth(), $today],
            'last_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
            default => [$today->subDays(6), $today],
        };

        return [$from->toDateString(), $to->toDateString(), in_array($period, ['today', '7d', '30d', 'month', 'last_month'], true) ? $period : '7d'];
    }

    private function sort(Request $request): string
    {
        return in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'last_used';
    }

    public function userStatusLabel(object $u, int $limit): string
    {
        if ($this->quota->limitEnabled() && (int) $u->today_success >= $limit) {
            return self::USER_STATUSES['quota_exhausted'];
        }

        return (int) $u->today_count > 0 ? self::USER_STATUSES['active_today'] : self::USER_STATUSES['idle_today'];
    }

    private function csv(string $filename, array $header, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $writer): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header, ';');
            $writer($out);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Cegah CSV/formula injection saat dibuka di Excel. */
    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    private function fmt($date): string
    {
        return $date ? $date->copy()->setTimezone($this->tz())->format('Y-m-d H:i:s') : '';
    }

    private function tz(): string
    {
        return app(\App\Services\AiAssistant\AiAssistantSettings::class)->timezone();
    }

    private function validDate(mixed $value): bool
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value) !== false;
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], mb_substr($value, 0, 100));
    }
}
