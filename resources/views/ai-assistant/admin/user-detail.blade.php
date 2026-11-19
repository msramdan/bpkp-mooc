@extends('layouts.app')

@section('title', __('Riwayat AI: :name', ['name' => $subject->name]))

@section('content')
    @php
        $tz = app(\App\Services\AiAssistant\AiAssistantSettings::class)->timezone();
        $num = fn ($v) => number_format((float) $v, 0, ',', '.');
        $statusLabels = ['success' => 'Berhasil', 'failed' => 'Gagal', 'rejected' => 'Ditolak', 'processing' => 'Diproses'];
    @endphp

    <div class="aiad">
        @include('ai-assistant.admin.partials.head', ['title' => __('Riwayat Chat User'), 'active' => 'users'])

        <section class="aiad-hero">
            <div class="aiad-hero__row">
                <span class="aiad-user__avatar" style="width:52px;height:52px;font-size:20px;border-radius:14px;background:rgba(255,255,255,.14)">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($subject->name, 0, 1)) }}
                </span>
                <div class="aiad-hero__title">
                    <div class="aiad-hero__eyebrow">{{ __('Riwayat percakapan AI') }}</div>
                    <h2>{{ $subject->name }}</h2>
                    <p>{{ $subject->email }} · {{ $subject->getRoleNames()->implode(', ') ?: '-' }}</p>
                </div>
                <div class="aiad-hero__figure">
                    <strong>{{ $quota['limit_enabled'] ? $quota['remaining'].'/'.$quota['limit'] : '∞' }}</strong>
                    <span>{{ __('sisa kuota hari ini') }}</span>
                </div>
            </div>
        </section>

        <div class="aiad-stats">
            <div class="aiad-stat"><span class="aiad-stat__icon"><i class="ri-question-answer-line"></i></span><span class="aiad-stat__label">{{ __('Total pertanyaan') }}</span><span class="aiad-stat__value">{{ $num($summary->total) }}</span></div>
            <div class="aiad-stat aiad-stat--good"><span class="aiad-stat__icon"><i class="ri-checkbox-circle-line"></i></span><span class="aiad-stat__label">{{ __('Berhasil') }}</span><span class="aiad-stat__value">{{ $num($summary->success) }}</span></div>
            <div class="aiad-stat aiad-stat--warn"><span class="aiad-stat__icon"><i class="ri-shield-cross-line"></i></span><span class="aiad-stat__label">{{ __('Ditolak') }}</span><span class="aiad-stat__value">{{ $num($summary->rejected) }}</span></div>
            <div class="aiad-stat aiad-stat--bad"><span class="aiad-stat__icon"><i class="ri-error-warning-line"></i></span><span class="aiad-stat__label">{{ __('Gagal') }}</span><span class="aiad-stat__value">{{ $num($summary->failed) }}</span></div>
            <div class="aiad-stat"><span class="aiad-stat__icon"><i class="ri-coins-line"></i></span><span class="aiad-stat__label">{{ __('Token / biaya') }}</span><span class="aiad-stat__value">{{ $num($summary->tokens) }}</span><span class="aiad-stat__sub">${{ number_format((float) $summary->cost, 4) }}</span></div>
        </div>

        <form class="aiad-filter" method="GET">
            <div class="aiad-filter__grow">
                <label class="form-label" for="f-q">{{ __('Cari di pertanyaan / jawaban') }}</label>
                <input type="search" id="f-q" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('Kata kunci…') }}">
            </div>
            <div>
                <label class="form-label" for="f-status">{{ __('Status') }}</label>
                <select id="f-status" name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Semua') }}</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ __($statusLabels[$s] ?? $s) }}</option>
                    @endforeach
                </select>
            </div>
            @include('ai-assistant.admin.partials.filter-dates')
            <div>
                <label class="form-label" for="f-order">{{ __('Urutan') }}</label>
                <select id="f-order" name="order" class="form-select form-select-sm">
                    <option value="asc" @selected(request('order', 'asc') === 'asc')>{{ __('Kronologis (lama → baru)') }}</option>
                    <option value="desc" @selected(request('order') === 'desc')>{{ __('Terbaru dulu') }}</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm px-3"><i class="ri-filter-3-line me-1"></i>{{ __('Terapkan') }}</button>
                @can('ai assistant monitor export')
                    <a href="{{ route('ai-assistant-logs.requests.export', array_merge(request()->only(['q', 'status', 'from', 'to']), ['user_id' => $subject->id])) }}" class="btn btn-outline-primary btn-sm"><i class="ri-download-2-line me-1"></i>{{ __('Export') }}</a>
                @endcan
            </div>
        </form>

        <div class="aiad-transcript">
            @forelse ($logs as $log)
                <article class="aiad-turn aiad-turn--{{ $log->status }}">
                    <div class="aiad-turn__meta">
                        @include('ai-assistant.admin.partials.status-badge', ['value' => $log->status])
                        @include('ai-assistant.admin.partials.status-badge', ['value' => $log->security_status])
                        <span><i class="ri-time-line"></i> <b>{{ $log->requested_at?->setTimezone($tz)->format('d M Y H:i:s') }}</b></span>
                        <span>{{ __('Respons') }}: <b>{{ $log->responded_at?->setTimezone($tz)->format('H:i:s') ?? '-' }}</b></span>
                        <span>{{ __('Durasi') }}: <b>{{ $log->duration_ms !== null ? number_format($log->duration_ms / 1000, 2, ',', '.').' dtk' : '-' }}</b></span>
                        <span>{{ __('Model') }}: <b>{{ $log->provider }}/{{ $log->model ?? '-' }}</b></span>
                        <span>{{ __('Token') }}: <b>{{ $log->input_tokens !== null ? $num($log->input_tokens).' / '.$num($log->output_tokens) : '-' }}</b></span>
                        <span>{{ __('Kategori') }}: <b>{{ $log->category ? str_replace('_', ' ', $log->category) : '-' }}</b></span>
                        <span>{{ __('Kuota') }}: <b>{{ $log->quota_counted ? $log->quota_used.'/'.$log->quota_limit : __('tidak dihitung') }}</b></span>
                        @if ($log->data_categories)
                            <span>{{ __('Data diakses') }}: <b>{{ implode(', ', $log->data_categories) }}</b></span>
                        @endif
                        <a href="{{ route('ai-assistant-logs.requests.show', $log->id) }}" class="ms-auto small">{{ __('Detail teknis') }} →</a>
                    </div>
                    <div class="aiad-turn__q">
                        <span class="aiad-turn__label">{{ __('Pertanyaan') }}</span>
                        <div class="aiad-turn__text">{{ $log->question }}</div>
                    </div>
                    <div class="aiad-turn__a">
                        <span class="aiad-turn__label">{{ __('Jawaban AI') }}</span>
                        <div class="aiad-turn__text">{{ $log->answer ?? '—' }}</div>
                    </div>
                    @if ($log->rejection_reason || $log->error_message)
                        <div class="aiad-turn__reason"><i class="ri-information-line me-1"></i>{{ $log->rejection_reason ?? $log->error_message }}</div>
                    @endif
                </article>
            @empty
                <div class="aiad-panel"><div class="aiad-empty"><i class="ri-chat-off-line"></i>{{ __('Tidak ada riwayat yang cocok.') }}</div></div>
            @endforelse
        </div>

        @if ($logs->hasPages())
            <div class="mt-3">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
