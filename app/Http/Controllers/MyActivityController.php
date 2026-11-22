<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "Aktivitas Saya" — setiap user (peserta maupun admin) hanya melihat aktivitas
 * yang DIA lakukan sendiri. Isolasi dijaga di query (scope ownedBy dengan user sesi),
 * tidak pernah memakai user_id dari URL/parameter.
 */
class MyActivityController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = ActivityLog::query()->ownedBy($user);

        if (array_key_exists((string) $request->query('log'), ActivityLog::LOG_NAMES)) {
            $query->where('log_name', $request->query('log'));
        }

        if ($request->filled('q')) {
            $term = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], mb_substr((string) $request->query('q'), 0, 100)).'%';
            $query->where('description', 'like', $term);
        }

        foreach (['from' => 'startOfDay', 'to' => 'endOfDay'] as $key => $edge) {
            $v = $request->query($key);
            if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) !== false) {
                $query->where('created_at', $key === 'from' ? '>=' : '<=', CarbonImmutable::parse($v, ActivityLogController::TZ)->{$edge}()->utc());
            }
        }

        $logs = $query->orderByDesc('id')->paginate(30)->withQueryString();

        $lastLogin = ActivityLog::query()->ownedBy($user)->where('log_name', 'auth')->where('event', 'login')->orderByDesc('id')->skip(1)->first();
        $failedLogins = ActivityLog::query()->ownedBy($user)->where('log_name', 'auth')->where('event', 'failed')
            ->where('created_at', '>=', now()->subDays(30))->count();

        return view('activity-logs.mine', [
            'logs' => $logs,
            'logNames' => ActivityLog::LOG_NAMES,
            'lastLogin' => $lastLogin,
            'failedLogins' => $failedLogins,
        ]);
    }

    public function show(Request $request, string $activity): View
    {
        // ID milik user lain -> 404 (tidak membocorkan keberadaan data).
        $log = ActivityLog::query()->ownedBy($request->user())->whereKey((int) $activity)->firstOrFail();

        $related = $log->request_id
            ? ActivityLog::query()->ownedBy($request->user())->where('request_id', $log->request_id)->whereKeyNot($log->id)->orderBy('id')->limit(50)->get()
            : collect();

        return view('activity-logs.show', ['log' => $log, 'related' => $related, 'admin' => false]);
    }
}
