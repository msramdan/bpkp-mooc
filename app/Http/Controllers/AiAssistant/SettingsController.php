<?php

namespace App\Http\Controllers\AiAssistant;

use App\Http\Controllers\Controller;
use App\Services\AiAssistant\AiAssistantSettings;
use App\Services\AiAssistant\AuditLogger;
use App\Services\AiAssistant\Provider\AiProvider;
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

    public function edit(Request $request, QuotaManager $quota): View
    {
        $this->audit->log($request, 'settings.viewed', 'Membuka AI Assistant Settings');

        $models = (array) $this->settings->get('available_models', []);
        $current = (string) $this->settings->get('model');
        if ($current !== '' && ! collect($models)->contains('id', $current)) {
            array_unshift($models, ['id' => $current, 'name' => $current]);
        }

        return view('ai-assistant.admin.settings', [
            's' => $this->settings->all(),
            'hasApiKey' => $this->settings->hasApiKey(),
            'apiKeyUpdatedAt' => $this->settings->apiKeyUpdatedAt(),
            'models' => $models,
            'connection' => (array) $this->settings->get('connection_status', []),
            'roles' => Role::query()->orderBy('name')->pluck('name'),
            'providers' => config('ai_assistant.providers'),
            'efforts' => config('ai_assistant.effort_levels'),
            'timezones' => config('ai_assistant.timezones'),
            'overrideAllowed' => $this->settings->securityOverrideAllowed(),
            'nextReset' => $quota->status($request->user())['reset_label'],
            'fakeProvider' => $this->fakeProvider(),
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);

        if ($data['enabled'] && ! $this->settings->hasApiKey() && ! $this->fakeProvider()) {
            return back()->withErrors(['enabled' => __('Simpan API key Anthropic terlebih dahulu sebelum mengaktifkan AI Assistant.')]);
        }

        $this->save($request, ['enabled' => (bool) $data['enabled']], 'settings.general_updated');

        return $this->back($data['enabled'] ? __('AI Assistant diaktifkan.') : __('AI Assistant dinonaktifkan. Riwayat percakapan tetap tersimpan.'), 'general');
    }

    public function updateProvider(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', Rule::in(array_keys(config('ai_assistant.providers')))],
            'provider_enabled' => ['required', 'boolean'],
            'model' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9][a-z0-9.\-@:]*$/i'],
            'effort' => ['nullable', Rule::in(config('ai_assistant.effort_levels'))],
            'max_output_tokens' => ['required', 'integer', 'min:256', 'max:32000'],
            'request_timeout' => ['required', 'integer', 'min:10', 'max:300'],
            'custom_instructions' => ['nullable', 'string', 'max:3000'],
        ], [
            'model.regex' => __('Nama model tidak valid.'),
        ]);

        $data['provider_enabled'] = (bool) $data['provider_enabled'];
        $data['effort'] = $data['effort'] ?? '';
        $data['custom_instructions'] = trim((string) ($data['custom_instructions'] ?? ''));

        $this->save($request, $data, 'settings.provider_updated');

        return $this->back(__('Konfigurasi provider disimpan.'), 'provider');
    }

    public function updateApiKey(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'api_key' => ['required', 'string', 'min:20', 'max:300', 'regex:/^\S+$/'],
        ], [
            'api_key.regex' => __('API key tidak boleh mengandung spasi.'),
        ]);

        $this->settings->setApiKey($data['api_key'], $request->user()->id);
        $this->settings->set(['connection_status' => []], $request->user()->id);

        // Nilai key tidak pernah dicatat.
        $this->audit->log($request, 'settings.api_key_updated', 'API key Anthropic diperbarui');

        return $this->back(__('API key tersimpan terenkripsi. Klik "Test Connection" untuk memastikan.'), 'provider');
    }

    public function destroyApiKey(Request $request): RedirectResponse
    {
        $this->settings->removeApiKey();
        $this->settings->set(['connection_status' => []], $request->user()->id);
        $this->audit->log($request, 'settings.api_key_removed', 'API key Anthropic dihapus');

        return $this->back(__('API key dihapus.'), 'provider');
    }

    public function testConnection(Request $request, AiProvider $provider): JsonResponse
    {
        $result = $provider->testConnection(['timeout' => $this->settings->int('request_timeout')]);

        $status = [
            'ok' => $result['ok'],
            'message' => $result['message'],
            'checked_at' => now()->setTimezone($this->settings->timezone())->format('d M Y H:i'),
        ];

        $values = ['connection_status' => $status];
        if ($result['ok'] && $result['models'] !== []) {
            $values['available_models'] = $result['models'];
        }
        $this->settings->set($values, $request->user()->id);

        $this->audit->log($request, 'settings.connection_tested', $result['ok'] ? 'Test koneksi berhasil' : 'Test koneksi gagal', metadata: ['ok' => $result['ok'], 'message' => $result['message']]);

        return response()->json($status + ['models' => $result['models']], $result['ok'] ? 200 : 422);
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
