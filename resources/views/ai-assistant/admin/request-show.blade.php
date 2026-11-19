@extends('layouts.app')

@section('title', __('Detail Log AI'))

@section('content')
    @php
        $tz = app(\App\Services\AiAssistant\AiAssistantSettings::class)->timezone();
        $rows = [
            'ID log' => $log->id,
            'User' => trim(($log->user_name ?? '-').' <'.($log->user_email ?? '-').'>').' (ID '.($log->user_id ?? '-').')',
            'Role' => $log->user_role ?? '-',
            'Conversation ID' => $log->conversation_id ?? '-',
            'Session (hash)' => $log->session_hash ? \Illuminate\Support\Str::limit($log->session_hash, 16, '…') : '-',
            'Waktu pertanyaan' => $log->requested_at?->setTimezone($tz)->format('d M Y H:i:s.v'),
            'Waktu respons' => $log->responded_at?->setTimezone($tz)->format('d M Y H:i:s.v') ?? '-',
            'Durasi' => $log->duration_ms !== null ? $log->duration_ms.' ms' : '-',
            'Provider / model' => ($log->provider ?? '-').' / '.($log->model ?? '-'),
            'Token input / output / cache' => $log->input_tokens !== null ? $log->input_tokens.' / '.$log->output_tokens.' / '.($log->cache_read_tokens ?? 0) : 'tidak dicatat',
            'Estimasi biaya' => $log->cost_usd !== null ? '$'.number_format((float) $log->cost_usd, 6) : '-',
            'Kategori' => $log->category ?? '-',
            'Kategori data diakses' => $log->data_categories ? implode(', ', $log->data_categories) : '-',
            'Kuota' => $log->quota_counted ? 'Dihitung ('.$log->quota_used.'/'.$log->quota_limit.')' : 'Tidak dihitung (terpakai '.($log->quota_used ?? 0).'/'.($log->quota_limit ?? '-').')',
            'Tanggal kuota' => $log->usage_date?->format('d M Y'),
            'Alasan penolakan' => $log->rejection_reason ?? '-',
            'Error' => $log->error_code ? $log->error_code.' — '.$log->error_message : '-',
            'IP / user agent' => ($log->ip_address ?? '-').' · '.\Illuminate\Support\Str::limit((string) $log->user_agent, 80),
        ];
    @endphp

    <div class="aiad">
        @include('ai-assistant.admin.partials.head', ['title' => __('Detail Log'), 'active' => 'requests'])

        <div class="row g-3">
            <div class="col-xl-7">
                <article class="aiad-turn aiad-turn--{{ $log->status }}">
                    <div class="aiad-turn__meta">
                        @include('ai-assistant.admin.partials.status-badge', ['value' => $log->status])
                        @include('ai-assistant.admin.partials.status-badge', ['value' => $log->security_status])
                    </div>
                    <div class="aiad-turn__q">
                        <span class="aiad-turn__label">{{ __('Pertanyaan') }}</span>
                        <div class="aiad-turn__text">{{ $log->question }}</div>
                    </div>
                    <div class="aiad-turn__a">
                        <span class="aiad-turn__label">{{ __('Jawaban AI') }}</span>
                        <div class="aiad-turn__text">{{ $log->answer ?? '—' }}</div>
                    </div>
                </article>
            </div>
            <div class="col-xl-5">
                <div class="aiad-panel">
                    <div class="aiad-panel__head"><h3>{{ __('Metadata audit') }}</h3></div>
                    <div class="aiad-panel__body">
                        <dl class="row small mb-0">
                            @foreach ($rows as $label => $value)
                                <dt class="col-5 text-muted fw-normal">{{ __($label) }}</dt>
                                <dd class="col-7 text-break">{{ $value }}</dd>
                            @endforeach
                        </dl>
                        @if ($log->metadata)
                            <div class="small text-muted mt-2 mb-1">{{ __('Metadata teknis') }}</div>
                            <pre class="small p-2 rounded mb-0" style="background:var(--aiad-soft);white-space:pre-wrap">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        @endif
                    </div>
                </div>
                @if ($log->user_id)
                    <a href="{{ route('ai-assistant-logs.users.show', $log->user_id) }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i>{{ __('Riwayat user ini') }}</a>
                @endif
            </div>
        </div>
    </div>
@endsection
