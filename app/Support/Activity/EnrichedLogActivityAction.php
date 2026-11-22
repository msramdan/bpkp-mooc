<?php

namespace App\Support\Activity;

use App\Models\AiAssistantSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Actions\LogActivityAction;
use Throwable;

/**
 * Memperkaya setiap aktivitas (termasuk perubahan model) dengan:
 * - konteks request (request_id, IP, perangkat, method, route, URL),
 * - snapshot pelaku (nama, email, role) agar log tetap terbaca walau user dihapus,
 * - penyamaran nilai sensitif pada before/after dan properties.
 */
class EnrichedLogActivityAction extends LogActivityAction
{
    protected function transformChanges(Model $activity): void
    {
        $changes = $activity->attribute_changes?->toArray();

        if ($changes) {
            $encryptedSetting = $activity->subject instanceof AiAssistantSetting && $activity->subject->is_encrypted;

            foreach (['attributes', 'old'] as $side) {
                if (! isset($changes[$side]) || ! is_array($changes[$side])) {
                    continue;
                }
                $changes[$side] = Sanitizer::clean($changes[$side]);
                if ($encryptedSetting && array_key_exists('value', $changes[$side])) {
                    $changes[$side]['value'] = Sanitizer::MASK;
                }
            }

            $activity->attribute_changes = collect($changes);
        }

        if ($activity->properties && $activity->properties->isNotEmpty()) {
            $activity->properties = collect(Sanitizer::clean($activity->properties->toArray()));
        }
    }

    protected function beforeActivityLogged(Model $activity): void
    {
        parent::beforeActivityLogged($activity);

        try {
            $this->applyContext($activity);
        } catch (Throwable) {
            // Konteks bersifat pelengkap; jangan pernah menggagalkan aksi utama.
        }
    }

    private function applyContext(Model $activity): void
    {
        $causer = $activity->causer;
        if (! $causer && $activity->causer_type === null && Auth::check()) {
            $causer = Auth::user();
            $activity->causer()->associate($causer);
        }

        if ($causer instanceof User) {
            $activity->causer_name ??= $causer->name;
            $activity->causer_email ??= $causer->email;
            $activity->causer_role ??= $causer->getRoleNames()->first();
        }

        $activity->request_id ??= app(RequestContext::class)->requestId();

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            $activity->method ??= 'CLI';

            return;
        }

        $request = request();
        $activity->ip_address ??= $request->ip();
        $activity->user_agent ??= mb_substr((string) $request->userAgent(), 0, 255);
        $activity->method ??= $request->method();
        $activity->route_name ??= $request->route()?->getName();
        $activity->url ??= mb_substr('/'.ltrim($request->path(), '/').self::safeQuery($request->query()), 0, 500);
    }

    public static function safeQuery(array $query): string
    {
        if ($query === []) {
            return '';
        }

        $clean = [];
        foreach ($query as $k => $v) {
            $clean[$k] = Sanitizer::isSensitiveKey($k) ? Sanitizer::MASK : (is_scalar($v) ? mb_substr((string) $v, 0, 100) : '[…]');
        }

        return '?'.http_build_query($clean);
    }
}
