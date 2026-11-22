<?php

namespace App\Http\Middleware;

use App\Support\Activity\ActivityDescriber;
use App\Support\Activity\RequestContext;
use App\Support\Activity\Sanitizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Catat setiap request user yang sudah login (halaman yang dibuka & aksi yang dilakukan).
 * Ditulis di terminate() — setelah respons terkirim — agar tidak memperlambat user.
 */
class LogRequestActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        // Disimpan di request (bukan properti) karena terminate() bisa memakai instance middleware baru.
        $request->attributes->set('activity_started_at', microtime(true));
        app(RequestContext::class)->requestId(); // inisialisasi request_id untuk seluruh aktivitas request ini

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            $user = $request->user();

            if (! $user || ! config('activitylog.enabled', true)) {
                return;
            }

            $route = $request->route();
            $routeName = $route?->getName();

            if ($routeName && in_array($routeName, (array) config('activitylog.ignore_routes', []), true)) {
                return;
            }

            $isBackground = $request->expectsJson() || $request->ajax();
            if ($routeName && $isBackground && in_array($routeName, (array) config('activitylog.ignore_background_routes', []), true)) {
                return;
            }

            $payload = $request->isMethod('GET') || $request->isMethod('HEAD')
                ? []
                : Sanitizer::clean($request->except(['_token', '_method']) + $this->files($request));

            $params = [];
            foreach ((array) $route?->parameters() as $key => $value) {
                $params[$key] = is_object($value) && method_exists($value, 'getKey') ? (string) $value->getKey() : (is_scalar($value) ? (string) $value : null);
            }

            activity('request')
                ->causedBy($user)
                ->event('request')
                ->withProperties(array_filter([
                    'route_params' => $params ?: null,
                    'payload' => $payload ?: null,
                    'ajax' => $request->expectsJson() ?: null,
                    'referer' => $request->headers->get('referer') ? mb_substr((string) $request->headers->get('referer'), 0, 300) : null,
                ], fn ($v) => $v !== null))
                ->tap(function ($activity) use ($request, $response): void {
                    $started = (float) $request->attributes->get('activity_started_at', microtime(true));
                    $activity->status_code = $response->getStatusCode();
                    $activity->duration_ms = (int) round((microtime(true) - $started) * 1000);
                })
                ->log(ActivityDescriber::forRoute($route, $request->method(), $request->path()));
        } catch (Throwable $e) {
            // Logging tidak boleh mengganggu aplikasi.
            report($e);
        }
    }

    private function files(Request $request): array
    {
        $out = [];
        foreach ($request->allFiles() as $key => $file) {
            $out[$key] = $file;
        }

        return $out;
    }
}
