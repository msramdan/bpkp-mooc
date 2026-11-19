<?php

namespace App\Http\Controllers\AiAssistant;

use App\Http\Controllers\Controller;
use App\Services\AiAssistant\AiAssistantSettings;
use App\Services\AiAssistant\AuditLogger;
use App\Services\AiAssistant\LogPruner;
use App\Services\AiAssistant\Provider\ProviderFactory;
use App\Services\AiAssistant\QuotaManager;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * AI Assistant Settings — hanya Super Admin. API key tidak pernah dikirim ke view.
 */
class SettingsController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly AiAssistantSettings $settings,
        private readonly AuditLogger $audit,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('role:'.Roles::SUPER_ADMIN),
            new Middleware('permission:ai assistant setting manage'),
        ];
    }

    public function edit(Request $request, QuotaManager $quota, LogPruner $pruner): View
    {
        $cleanup = $pruner->preview();

        $this->audit->log($request, 'settings.viewed', 'Membuka AI Assistant Settings');

        $providers = [];
        foreach ($this->settings->providers() as $key => $meta) {
            $models = $this->settings->availableModels($key);
            $current = $this->settings->modelFor($key);
            if ($current !== '' && ! collect($models)->contains('id', $current)) {
                array_unshift($models, ['id' => $current, 'name' => $current]);
            }

            $providers[$key] = $meta + [
                'key' => $key,
                'active' => $this->settings->activeProvider() === $key,
                'has_key' => $this->settings->hasApiKey($key),
                'key_updated_at' => $this->settings->apiKeyUpdatedAt($key),
                'model' => $current,
                'models' => $models,
                'connection' => $this->settings->connectionStatus($key),
            ];
        }

        return view('ai-assistant.admin.settings', [
            's' => $this->settings->all(),
            'activeProvider' => $this->settings->activeProvider(),
            'hasApiKey' => $this->settings->hasApiKey($this->settings->activeProvider()),
            'providers' => $providers,
            'roles' => Role::query()->orderBy('name')->pluck('name'),
            'efforts' => config('ai_assistant.effort_levels'),
            'timezones' => config('ai_assistant.timezones'),
            'overrideAllowed' => $this->settings->securityOverrideAllowed(),
            'nextReset' => $quota->status($request->user())['reset_label'],
            'fakeProvider' => $this->fakeProvider(),
            'cleanup' => $cleanup,
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);

        $active = $this->settings->activeProvider();
        if ($data['enabled'] && ! $this->settings->hasApiKey($active) && ! $this->fakeProvider()) {
            return back()->withErrors(['enabled' => __('Simpan API key :p terlebih dahulu sebelum mengaktifkan AI Assistant.', ['p' => $this->label($active)])]);
        }

        $this->save($request, ['enabled' => (bool) $data['enabled']], 'settings.general_updated');

        return $this->back($data['enabled'] ? __('AI Assistant diaktifkan.') : __('AI Assistant dinonaktifkan. Riwayat percakapan tetap tersimpan.'), 'general');
    }

    public function updateProvider(Request $request): RedirectResponse
    {
        // Pengaturan umum yang berlaku untuk semua provider.
        $data = $request->validate([
            'provider_enabled' => ['required', 'boolean'],
            'effort' => ['nullable', Rule::in(config('ai_assistant.effort_levels'))],
            'max_output_tokens' => ['required', 'integer', 'min:256', 'max:32000'],
            'request_timeout' => ['required', 'integer', 'min:10', 'max:300'],
            'custom_instructions' => ['nullable', 'string', 'max:3000'],
        ]);

        $data['provider_enabled'] = (bool) $data['provider_enabled'];
        $data['effort'] = $data['effort'] ?? '';
        $data['custom_instructions'] = trim((string) ($data['custom_instructions'] ?? ''));

        $this->save($request, $data, 'settings.provider_updated');

        return $this->back(__('Konfigurasi provider disimpan.'), 'provider');
    }

    /** Ganti provider aktif. Provider tujuan wajib punya API key; provider + model disimpan atomik. */
    public function switchProvider(Request $request): RedirectResponse
    {
        $target = $this->target($request, 'provider');
        $current = $this->settings->activeProvider();

        if ($target === $current) {
            return $this->back(__(':p sudah menjadi provider aktif.', ['p' => $this->label($target)]), 'provider');
        }

        if (! $this->settings->hasApiKey($target) && ! $this->fakeProvider()) {
            return back()->withErrors(['provider' => __('Simpan API key :p terlebih dahulu sebelum menjadikannya provider aktif.', ['p' => $this->label($target)])])->withFragment('provider');
        }

        $this->save($request, ['provider' => $target], 'settings.provider_switched');

        return $this->back(__('Provider aktif sekarang :p (model :m). Request yang sedang berjalan tetap diselesaikan oleh provider sebelumnya.', [
            'p' => $this->label($target),
            'm' => $this->settings->modelFor($target),
        ]), 'provider');
    }

    public function updateModel(Request $request): RedirectResponse
    {
        $target = $this->target($request);
        $data = $request->validate([
            'model' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9][a-z0-9._\-\/@:]*$/i'],
        ], ['model.regex' => __('Nama model tidak valid.')]);

        $this->save($request, [$this->settings->modelKey($target) => trim($data['model'])], 'settings.model_updated');

        return $this->back(__('Model :p disimpan: :m', ['p' => $this->label($target), 'm' => trim($data['model'])]), 'provider');
    }

    public function updateApiKey(Request $request): RedirectResponse
    {
        $target = $this->target($request);
        $data = $request->validate([
            'api_key' => ['required', 'string', 'min:20', 'max:300', 'regex:/^\S+$/'],
        ], [
            'api_key.regex' => __('API key tidak boleh mengandung spasi.'),
        ]);

        $this->settings->setApiKey($data['api_key'], $request->user()->id, $target);
        $this->settings->set(["connection_status_{$target}" => []], $request->user()->id);

        // Nilai key tidak pernah dicatat.
        $this->audit->log($request, 'settings.api_key_updated', 'API key '.$this->label($target).' diperbarui', metadata: ['provider' => $target]);

        return $this->back(__('API key :p tersimpan terenkripsi. Klik "Test Connection" untuk memastikan.', ['p' => $this->label($target)]), 'provider');
    }

    public function destroyApiKey(Request $request): RedirectResponse
    {
        $target = $this->target($request);

        if ($target === $this->settings->activeProvider() && $this->settings->bool('enabled')) {
            return back()->withErrors(['api_key' => __('API key provider aktif tidak bisa dihapus saat AI Assistant ON. Matikan fitur atau pindah provider terlebih dahulu.')])->withFragment('provider');
        }

        $this->settings->removeApiKey($target);
        $this->settings->set(["connection_status_{$target}" => []], $request->user()->id);
        $this->audit->log($request, 'settings.api_key_removed', 'API key '.$this->label($target).' dihapus', metadata: ['provider' => $target]);

        return $this->back(__('API key :p dihapus.', ['p' => $this->label($target)]), 'provider');
    }

    public function testConnection(Request $request, ProviderFactory $factory): JsonResponse
    {
        $target = $this->target($request);
        $result = $factory->make($target)->testConnection(['timeout' => $this->settings->int('request_timeout')]);

        $status = [
            'ok' => $result['ok'],
            'message' => $result['message'],
            'balance' => $result['balance'] ?? null,
            'checked_at' => now()->setTimezone($this->settings->timezone())->format('d M Y H:i'),
        ];

        $values = ["connection_status_{$target}" => $status];
        if ($result['ok'] && $result['models'] !== []) {
            $values["available_models_{$target}"] = $result['models'];
        }
        $this->settings->set($values, $request->user()->id);

        $this->audit->log($request, 'settings.connection_tested', ($result['ok'] ? 'Test koneksi berhasil: ' : 'Test koneksi gagal: ').$this->label($target), metadata: ['provider' => $target, 'ok' => $result['ok'], 'message' => $result['message']]);

        return response()->json($status + ['provider' => $target, 'models' => $result['models']], $result['ok'] ? 200 : 422);
    }

    private function target(Request $request, string $field = 'target'): string
    {
        $value = (string) $request->input($field, $this->settings->activeProvider());
        abort_unless(array_key_exists($value, $this->settings->providers()), 422, __('Provider tidak dikenal.'));

        return $value;
    }

    private function label(string $provider): string
    {
        return (string) ($this->settings->providers()[$provider]['label'] ?? $provider);
    }

    public function updateLimits(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'usage_limit_enabled' => ['required', 'boolean'],
            'daily_limit' => ['required', 'integer', 'min:1', 'max:1000'],
            'reset_hour' => ['required', 'integer', 'min:0', 'max:23'],
            'timezone' => ['required', Rule::in(array_keys(config('ai_assistant.timezones')))],
            'log_retention_days' => ['required', 'integer', 'min:30', 'max:3650'],
            'history_window' => ['required', 'integer', 'min:0', 'max:30'],
            'max_question_length' => ['required', 'integer', 'min:100', 'max:4000'],
        ]);

        $data['usage_limit_enabled'] = (bool) $data['usage_limit_enabled'];

        $this->save($request, $data, 'settings.limits_updated');

        return $this->back(__('Pengaturan limit disimpan.'), 'limits');
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        $rules = ['token_usage_tracking_enabled' => ['required', 'boolean']];
        $overrideAllowed = $this->settings->securityOverrideAllowed();

        if ($overrideAllowed) {
            foreach (AiAssistantSettings::CORE_SECURITY_KEYS as $key) {
                $rules[$key] = ['required', 'boolean'];
            }
            $rules['confirm_security'] = ['nullable', 'accepted'];
        }

        $data = $request->validate($rules);
        $values = ['token_usage_tracking_enabled' => (bool) $data['token_usage_tracking_enabled']];

        if ($overrideAllowed) {
            $disabling = collect(AiAssistantSettings::CORE_SECURITY_KEYS)->filter(fn ($k) => ! $data[$k])->values();
            if ($disabling->isNotEmpty() && ! $request->boolean('confirm_security')) {
                return back()->withErrors(['confirm_security' => __('Centang konfirmasi untuk menonaktifkan proteksi keamanan inti.')])->withFragment('security');
            }
            foreach (AiAssistantSettings::CORE_SECURITY_KEYS as $key) {
                $values[$key] = (bool) $data[$key];
            }
        }

        $this->save($request, $values, 'settings.security_updated');

        return $this->back(__('Pengaturan keamanan disimpan.'), 'security');
    }

    public function updateAccess(Request $request): RedirectResponse
    {
        $roles = Role::query()->pluck('name')->all();
        $data = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in($roles)],
        ]);

        $enabled = $data['roles'] ?? [];
        $map = collect($roles)->mapWithKeys(fn ($r) => [$r => in_array($r, $enabled, true)])->all();

        $this->save($request, ['role_access' => $map], 'settings.access_updated');

        return $this->back(__('Akses per role disimpan.'), 'access');
    }

    /** Pembersihan data lama secara manual oleh Super Admin (tanpa cron). */
    public function prune(Request $request, LogPruner $pruner): RedirectResponse
    {
        $request->validate(['confirm' => ['accepted']], ['confirm.accepted' => __('Centang konfirmasi untuk menghapus data lama.')]);

        $result = $pruner->prune();
        $total = $result['request_logs'] + $result['conversations'] + $result['audit_logs'] + $result['usages'];

        $this->audit->log($request, 'settings.logs_pruned', "Membersihkan data AI sebelum {$result['cutoff']}", metadata: $result);

        return $this->back($total > 0
            ? __(':n data lama (sebelum :date) berhasil dihapus.', ['n' => number_format($total, 0, ',', '.'), 'date' => $result['cutoff']])
            : __('Tidak ada data yang melewati masa retensi.'), 'cleanup');
    }

    private function save(Request $request, array $values, string $action): void
    {
        $before = collect($this->settings->all())->only(array_keys($values))->all();
        $this->settings->set($values, $request->user()->id);

        $changes = [];
        foreach ($values as $key => $value) {
            if (($before[$key] ?? null) !== $value) {
                $changes[$key] = ['dari' => $before[$key] ?? null, 'menjadi' => $value];
            }
        }

        $this->audit->log($request, $action, 'Mengubah pengaturan AI Assistant', metadata: ['perubahan' => $changes]);
    }

    private function fakeProvider(): bool
    {
        return (bool) config('ai_assistant.fake_provider') && app()->environment(['local', 'testing']);
    }

    private function back(string $message, string $section): RedirectResponse
    {
        return redirect()->to(route('ai-assistant-settings.index').'#'.$section)->with('success', $message);
    }
}
