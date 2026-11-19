@extends('layouts.app')

@section('title', __('AI Assistant Settings'))

@section('content')
    @php
        $on = (bool) $s['enabled'];
        $switch = function (string $name, bool $checked, bool $disabled = false) {
            return '<input type="hidden" name="'.$name.'" value="0">'.
                '<div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" name="'.$name.'" value="1" id="sw-'.$name.'"'.
                ($checked ? ' checked' : '').($disabled ? ' disabled' : '').'></div>';
        };
        $securityItems = [
            'security_firewall_enabled' => ['Security Firewall', 'Menolak permintaan ENV, API key, password DB, source code, dan SQL sebelum sampai ke AI.'],
            'prompt_injection_protection_enabled' => ['Prompt Injection Protection', 'Menolak upaya mengubah aturan/peran AI atau meminta system prompt.'],
            'sensitive_data_protection_enabled' => ['Sensitive Data Protection', 'Menyamarkan NIP dan menyensor email/NIP orang lain serta pola rahasia pada jawaban AI.'],
        ];
    @endphp

    <div class="aiad">
        @include('ai-assistant.admin.partials.head', ['title' => __('AI Assistant Settings'), 'active' => 'settings'])

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="aiad-settings">
            <nav class="aiad-settings__nav" aria-label="{{ __('Bagian pengaturan') }}">
                <a href="#general"><i class="ri-shut-down-line"></i>{{ __('Status fitur') }}<span class="k">A</span></a>
                <a href="#provider"><i class="ri-cpu-line"></i>{{ __('Provider AI') }}<span class="k">B</span></a>
                <a href="#limits"><i class="ri-ticket-2-line"></i>{{ __('Limit & kuota') }}<span class="k">C</span></a>
                <a href="#security"><i class="ri-shield-keyhole-line"></i>{{ __('Keamanan') }}<span class="k">D</span></a>
                <a href="#access"><i class="ri-team-line"></i>{{ __('Akses role') }}<span class="k">E</span></a>
                <a href="#cleanup"><i class="ri-delete-bin-6-line"></i>{{ __('Bersihkan data') }}<span class="k">F</span></a>
            </nav>

            <div>
                {{-- A. Global enable/disable --}}
                <section class="aiad-panel aiad-section" id="general">
                    <div class="aiad-panel__head"><div><h3><span class="k">A</span>{{ __('Status AI Assistant') }}</h3><p>{{ __('Saat OFF, endpoint backend menolak semua request AI dan menu disembunyikan. Riwayat tetap tersimpan.') }}</p></div></div>
                    <div class="aiad-panel__body">
                        <form method="POST" action="{{ route('ai-assistant-settings.general') }}" class="aiad-power {{ $on ? 'is-on' : '' }}">
                            @csrf @method('PUT')
                            <div class="d-flex align-items-center gap-3">
                                <span class="aiad-stat__icon" style="width:48px;height:48px;font-size:22px"><i class="ri-sparkling-2-line"></i></span>
                                <div class="aiad-power__state">
                                    AI Assistant: {{ $on ? 'ON' : 'OFF' }}
                                    <small>{{ $on ? __('Dapat digunakan sesuai kuota & aturan keamanan.') : __('Tidak dapat digunakan oleh seluruh user.') }}</small>
                                </div>
                            </div>
                            <input type="hidden" name="enabled" value="{{ $on ? 0 : 1 }}">
                            <button type="submit" class="btn {{ $on ? 'btn-outline-danger' : 'btn-success' }}"
                                @if (! $on && ! $hasApiKey && ! $fakeProvider) disabled title="{{ __('Isi API key terlebih dahulu') }}" @endif>
                                <i class="ri-shut-down-line me-1"></i>{{ $on ? __('Matikan') : __('Aktifkan') }}
                            </button>
                        </form>
                        @if (! $hasApiKey && ! $on)
                            <p class="small text-muted mt-2 mb-0"><i class="ri-information-line"></i> {{ __('Simpan API key Anthropic di bagian B sebelum mengaktifkan.') }}</p>
                        @endif
                    </div>
                </section>

                {{-- B. Provider --}}
                <section class="aiad-panel aiad-section" id="provider">
                    <div class="aiad-panel__head"><div><h3><span class="k">B</span>{{ __('Konfigurasi Provider AI') }}</h3><p>{{ __('Provider utama: Anthropic Claude. API key disimpan terenkripsi dan tidak pernah ditampilkan kembali.') }}</p></div></div>
                    <div class="aiad-panel__body">
                        <div class="aiad-keybox mb-3">
                            <i class="{{ $hasApiKey ? 'ri-key-2-fill text-success' : 'ri-key-2-line text-muted' }}"></i>
                            <div class="flex-grow-1">
                                @if ($hasApiKey)
                                    <strong>{{ __('API key tersimpan (terenkripsi)') }}</strong>
                                    <div class="small text-muted">{{ __('Diperbarui :at. Nilai key tidak ditampilkan.', ['at' => $apiKeyUpdatedAt?->setTimezone($s['timezone'])->format('d M Y H:i')]) }}</div>
                                @else
                                    <strong>{{ __('Belum ada API key') }}</strong>
                                    <div class="small text-muted">{{ __('Buat API key di console Anthropic, lalu tempel di bawah.') }}</div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="aiTestConn" data-url="{{ route('ai-assistant-settings.test-connection') }}" @disabled(! $hasApiKey && ! $fakeProvider)>
                                <i class="ri-plug-line me-1"></i>{{ __('Test Connection') }}
                            </button>
                        </div>
                        <div id="aiConnResult" class="aiad-conn mb-3 {{ empty($connection) ? 'd-none' : ($connection['ok'] ? 'is-ok' : 'is-bad') }}">
                            @if (! empty($connection))
                                {{ $connection['message'] }} · {{ __('dicek :at', ['at' => $connection['checked_at']]) }}
                            @endif
                        </div>

                        <form method="POST" action="{{ route('ai-assistant-settings.api-key') }}" class="row g-2 align-items-end mb-4" autocomplete="off">
                            @csrf @method('PUT')
                            <div class="col-md">
                                <label class="form-label small" for="api_key">{{ $hasApiKey ? __('Update API Key') : __('API Key') }}</label>
                                <input type="password" name="api_key" id="api_key" class="form-control" placeholder="sk-ant-…" autocomplete="new-password" spellcheck="false">
                            </div>
                            <div class="col-md-auto d-flex gap-2">
                                <button class="btn btn-primary"><i class="ri-lock-line me-1"></i>{{ $hasApiKey ? __('Update API Key') : __('Simpan API Key') }}</button>
                            </div>
                        </form>
                        @if ($hasApiKey)
                            <form method="POST" action="{{ route('ai-assistant-settings.api-key.destroy') }}" class="mb-4" onsubmit="return confirm('{{ __('Hapus API key? AI Assistant tidak akan bisa menjawab sampai key baru disimpan.') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-link btn-sm text-danger p-0"><i class="ri-delete-bin-line me-1"></i>{{ __('Hapus API key') }}</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('ai-assistant-settings.provider') }}">
                            @csrf @method('PUT')
                            <div class="aiad-switch-row pt-0">
                                <div><div class="aiad-switch-row__title">{{ __('Enable Provider') }}</div><div class="aiad-switch-row__desc">{{ __('Matikan sementara pemanggilan ke Anthropic tanpa mematikan menu.') }}</div></div>
                                {!! $switch('provider_enabled', (bool) $s['provider_enabled']) !!}
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-4">
                                    <label class="form-label small" for="provider">{{ __('Provider') }}</label>
                                    <select name="provider" id="provider" class="form-select">
                                        @foreach ($providers as $key => $label)
                                            <option value="{{ $key }}" @selected($s['provider'] === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small" for="model">{{ __('Model Claude') }}</label>
                                    <input type="text" name="model" id="model" list="aiModels" value="{{ old('model', $s['model']) }}" class="form-control" required>
                                    <datalist id="aiModels">
                                        @foreach ($models as $m)
                                            <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                                        @endforeach
                                    </datalist>
                                    <div class="form-text">{{ __('Daftar model aktif diambil dari Anthropic saat Test Connection (model deprecated tidak ditampilkan).') }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small" for="effort">{{ __('Effort (kedalaman berpikir)') }}</label>
                                    <select name="effort" id="effort" class="form-select">
                                        <option value="" @selected($s['effort'] === '')>{{ __('Bawaan model') }}</option>
                                        @foreach ($efforts as $e)
                                            <option value="{{ $e }}" @selected($s['effort'] === $e)>{{ ucfirst($e) }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">{{ __('Low = cepat & hemat, cocok untuk chat.') }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small" for="max_output_tokens">{{ __('Max output tokens') }}</label>
                                    <input type="number" name="max_output_tokens" id="max_output_tokens" min="256" max="32000" value="{{ old('max_output_tokens', $s['max_output_tokens']) }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small" for="request_timeout">{{ __('Request timeout (detik)') }}</label>
                                    <input type="number" name="request_timeout" id="request_timeout" min="10" max="300" value="{{ old('request_timeout', $s['request_timeout']) }}" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small" for="temperature">{{ __('Temperature') }}</label>
                                    <input type="text" id="temperature" class="form-control" value="{{ __('Tidak didukung') }}" disabled>
                                    <div class="form-text">{{ __('API Anthropic versi terbaru tidak lagi menerima parameter temperature untuk model Claude saat ini; gaya jawaban diatur lewat Effort dan instruksi.') }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small" for="custom_instructions">{{ __('System prompt tambahan') }}</label>
                                    <textarea name="custom_instructions" id="custom_instructions" rows="4" maxlength="3000" class="form-control" placeholder="{{ __('Contoh: Selalu ingatkan peserta tenggat kursus yang kurang dari 7 hari.') }}">{{ old('custom_instructions', $s['custom_instructions']) }}</textarea>
                                    <div class="form-text">{{ __('Ditambahkan setelah aturan keamanan inti. Aturan inti (isolasi data, larangan membuka rahasia sistem) tidak bisa ditimpa dari sini.') }}</div>
                                </div>
                            </div>
                            <div class="mt-3"><button class="btn btn-primary"><i class="ri-save-3-line me-1"></i>{{ __('Save Configuration') }}</button></div>
                        </form>
                    </div>
                </section>

                {{-- C. Limit --}}
                <section class="aiad-panel aiad-section" id="limits">
                    <div class="aiad-panel__head"><div><h3><span class="k">C</span>{{ __('Pengaturan Limit') }}</h3><p>{{ __('Kuota per user per hari (bukan per perangkat). Reset berikutnya: :at.', ['at' => $nextReset]) }}</p></div></div>
                    <div class="aiad-panel__body">
                        <form method="POST" action="{{ route('ai-assistant-settings.limits') }}">
                            @csrf @method('PUT')
                            <div class="row g-4 align-items-center">
                                <div class="col-md-6">
                                    <label class="form-label small d-block" for="daily_limit">{{ __('Maksimal pertanyaan per user per hari') }}</label>
                                    <div class="aiad-stepper">
                                        <button type="button" data-step="-1" aria-label="{{ __('Kurangi') }}">−</button>
                                        <input type="number" name="daily_limit" id="daily_limit" min="1" max="1000" value="{{ old('daily_limit', $s['daily_limit']) }}" class="form-control">
                                        <button type="button" data-step="1" aria-label="{{ __('Tambah') }}">+</button>
                                    </div>
                                    <div class="form-text">{{ __('Default 5. Pertanyaan yang gagal diproses sistem tidak mengurangi kuota.') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="aiad-switch-row py-0 border-0">
                                        <div><div class="aiad-switch-row__title">{{ __('Enable Usage Limit') }}</div><div class="aiad-switch-row__desc">{{ __('Jika dimatikan, penggunaan tetap dicatat lengkap.') }}</div></div>
                                        {!! $switch('usage_limit_enabled', (bool) $s['usage_limit_enabled']) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small" for="reset_hour">{{ __('Waktu reset kuota') }}</label>
                                    <select name="reset_hour" id="reset_hour" class="form-select">
                                        @for ($h = 0; $h < 24; $h++)
                                            <option value="{{ $h }}" @selected((int) $s['reset_hour'] === $h)>{{ sprintf('%02d:00', $h) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small" for="timezone">{{ __('Timezone') }}</label>
                                    <select name="timezone" id="timezone" class="form-select">
                                        @foreach ($timezones as $tz => $label)
                                            <option value="{{ $tz }}" @selected($s['timezone'] === $tz)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small" for="max_question_length">{{ __('Panjang maksimal pertanyaan') }}</label>
                                    <input type="number" name="max_question_length" id="max_question_length" min="100" max="4000" value="{{ old('max_question_length', $s['max_question_length']) }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small" for="history_window">{{ __('Konteks percakapan (pasangan tanya-jawab terakhir)') }}</label>
                                    <input type="number" name="history_window" id="history_window" min="0" max="30" value="{{ old('history_window', $s['history_window']) }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small" for="log_retention_days">{{ __('Retensi log (hari)') }}</label>
                                    <input type="number" name="log_retention_days" id="log_retention_days" min="30" max="3650" value="{{ old('log_retention_days', $s['log_retention_days']) }}" class="form-control">
                                </div>
                            </div>
                            <div class="mt-3"><button class="btn btn-primary"><i class="ri-save-3-line me-1"></i>{{ __('Simpan Limit') }}</button></div>
                        </form>
                    </div>
                </section>

                {{-- D. Security --}}
                <section class="aiad-panel aiad-section" id="security">
                    <div class="aiad-panel__head"><div><h3><span class="k">D</span>{{ __('Pengaturan Security') }}</h3><p>{{ __('Proteksi inti selalu aktif. Hanya bisa diubah bila server mengizinkan (AI_ASSISTANT_ALLOW_SECURITY_OVERRIDE).') }}</p></div></div>
                    <div class="aiad-panel__body">
                        <form method="POST" action="{{ route('ai-assistant-settings.security') }}">
                            @csrf @method('PUT')
                            @foreach ($securityItems as $key => [$title, $desc])
                                <div class="aiad-switch-row">
                                    <div><div class="aiad-switch-row__title">{{ $title }}</div><div class="aiad-switch-row__desc">{{ __($desc) }}</div></div>
                                    @if ($overrideAllowed)
                                        {!! $switch($key, (bool) $s[$key]) !!}
                                    @else
                                        <span class="aiad-lock"><i class="ri-lock-2-fill"></i>{{ __('Wajib aktif') }}</span>
                                    @endif
                                </div>
                            @endforeach
                            <div class="aiad-switch-row">
                                <div><div class="aiad-switch-row__title">User Data Isolation</div><div class="aiad-switch-row__desc">{{ __('AI hanya memakai fungsi whitelist dengan user dari sesi login. Tidak bisa dimatikan.') }}</div></div>
                                <span class="aiad-lock"><i class="ri-lock-2-fill"></i>{{ __('Selalu aktif') }}</span>
                            </div>
                            <div class="aiad-switch-row">
                                <div><div class="aiad-switch-row__title">Conversation Audit Log</div><div class="aiad-switch-row__desc">{{ __('Seluruh pertanyaan, jawaban, dan penolakan dicatat. Tidak bisa dimatikan.') }}</div></div>
                                <span class="aiad-lock"><i class="ri-lock-2-fill"></i>{{ __('Selalu aktif') }}</span>
                            </div>
                            <div class="aiad-switch-row">
                                <div><div class="aiad-switch-row__title">Token Usage Tracking</div><div class="aiad-switch-row__desc">{{ __('Catat jumlah token & estimasi biaya per request.') }}</div></div>
                                {!! $switch('token_usage_tracking_enabled', (bool) $s['token_usage_tracking_enabled']) !!}
                            </div>
                            @if ($overrideAllowed)
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="confirm_security" value="1" id="confirm_security">
                                    <label class="form-check-label small text-danger" for="confirm_security">{{ __('Saya memahami risiko menonaktifkan proteksi keamanan inti.') }}</label>
                                </div>
                            @endif
                            <div class="mt-3"><button class="btn btn-primary"><i class="ri-save-3-line me-1"></i>{{ __('Simpan Keamanan') }}</button></div>
                        </form>
                    </div>
                </section>

                {{-- E. Role access --}}
                <section class="aiad-panel aiad-section" id="access">
                    <div class="aiad-panel__head"><div><h3><span class="k">E</span>{{ __('Pengaturan Akses Role') }}</h3><p>{{ __('Default aktif untuk semua role. Data yang bisa diakses AI tetap hanya milik akun masing-masing.') }}</p></div></div>
                    <div class="aiad-panel__body">
                        <form method="POST" action="{{ route('ai-assistant-settings.access') }}">
                            @csrf @method('PUT')
                            <div class="row g-2">
                                @foreach ($roles as $role)
                                    @php $allowed = (bool) (($s['role_access'][$role] ?? true)); @endphp
                                    <div class="col-md-6">
                                        <label class="aiad-role" for="role-{{ $role }}">
                                            <span><i class="ri-user-settings-line me-2 text-muted"></i><strong>{{ \Illuminate\Support\Str::headline($role) }}</strong></span>
                                            <span class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" role="switch" name="roles[]" value="{{ $role }}" id="role-{{ $role }}" @checked($allowed)>
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3"><button class="btn btn-primary"><i class="ri-save-3-line me-1"></i>{{ __('Simpan Akses') }}</button></div>
                        </form>
                    </div>
                </section>

                {{-- F. Pembersihan data lama (manual, tanpa cron) --}}
                @php $cleanupTotal = $cleanup['request_logs'] + $cleanup['conversations'] + $cleanup['audit_logs'] + $cleanup['usages']; @endphp
                <section class="aiad-panel aiad-section" id="cleanup">
                    <div class="aiad-panel__head"><div><h3><span class="k">F</span>{{ __('Pembersihan Data Lama') }}</h3><p>{{ __('Hapus manual data AI yang lebih tua dari masa retensi (:days hari, diatur di bagian C). Tidak ada penghapusan otomatis.', ['days' => $cleanup['days']]) }}</p></div></div>
                    <div class="aiad-panel__body">
                        <div class="row g-2 mb-3">
                            @foreach (['request_logs' => 'Log pertanyaan', 'conversations' => 'Percakapan', 'audit_logs' => 'Audit admin', 'usages' => 'Data kuota harian'] as $key => $label)
                                <div class="col-6 col-md-3">
                                    <div class="aiad-stat" style="min-height:auto">
                                        <span class="aiad-stat__label">{{ __($label) }}</span>
                                        <span class="aiad-stat__value" style="font-size:20px">{{ number_format($cleanup[$key], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="small text-muted">{{ __('Data sebelum :date yang akan dihapus permanen. Data terbaru tidak tersentuh.', ['date' => \Carbon\Carbon::parse($cleanup['cutoff'])->translatedFormat('d M Y')]) }}</p>

                        @if ($cleanupTotal > 0)
                            <form method="POST" action="{{ route('ai-assistant-settings.prune') }}" onsubmit="return confirm('{{ __('Hapus permanen :n data lama? Tindakan ini tidak bisa dibatalkan.', ['n' => $cleanupTotal]) }}')">
                                @csrf
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="confirm" value="1" id="confirm_cleanup" required>
                                    <label class="form-check-label small" for="confirm_cleanup">{{ __('Saya paham data yang dihapus tidak bisa dikembalikan.') }}</label>
                                </div>
                                <button class="btn btn-outline-danger"><i class="ri-delete-bin-6-line me-1"></i>{{ __('Bersihkan :n data lama', ['n' => number_format($cleanupTotal, 0, ',', '.')]) }}</button>
                            </form>
                        @else
                            <div class="aiad-conn is-ok"><i class="ri-checkbox-circle-line me-1"></i>{{ __('Tidak ada data yang melewati masa retensi.') }}</div>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            document.querySelectorAll('.aiad-stepper [data-step]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var input = btn.parentElement.querySelector('input');
                    var v = (parseInt(input.value, 10) || 0) + parseInt(btn.dataset.step, 10);
                    input.value = Math.max(parseInt(input.min, 10), Math.min(parseInt(input.max, 10), v));
                });
            });

            var test = document.getElementById('aiTestConn');
            var out = document.getElementById('aiConnResult');
            if (!test) return;
            test.addEventListener('click', function () {
                var label = test.innerHTML;
                test.disabled = true;
                test.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menguji…';
                fetch(test.dataset.url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    credentials: 'same-origin',
                }).then(function (r) { return r.json(); }).then(function (d) {
                    out.className = 'aiad-conn mb-3 ' + (d.ok ? 'is-ok' : 'is-bad');
                    out.textContent = d.message + ' · dicek ' + (d.checked_at || '');
                    var list = document.getElementById('aiModels');
                    if (d.ok && d.models && list) {
                        list.innerHTML = '';
                        d.models.forEach(function (m) {
                            var o = document.createElement('option');
                            o.value = m.id; o.textContent = m.name;
                            list.appendChild(o);
                        });
                    }
                }).catch(function () {
                    out.className = 'aiad-conn mb-3 is-bad';
                    out.textContent = 'Gagal menghubungi server.';
                }).finally(function () { test.disabled = false; test.innerHTML = label; });
            });
        })();
    </script>
@endpush
