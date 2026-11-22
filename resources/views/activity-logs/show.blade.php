@extends('layouts.app')

@section('title', __('Detail Aktivitas'))

@section('content')
    @include('activity-logs.partials.head')
    @php
        $tz = \App\Http\Controllers\ActivityLogController::TZ;
        $diff = $log->diff();
        $props = $log->properties?->toArray() ?? [];
        $indexRoute = $admin ? route('activity-logs.index') : route('activity.mine');
        $showRoute = fn ($l) => $admin ? route('activity-logs.show', $l) : route('activity.mine.show', $l);
    @endphp

    <div class="alog">
        <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Detail Aktivitas') }}</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ $indexRoute }}">{{ $admin ? __('Log Aktivitas') : __('Aktivitas Saya') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $log->id }}</li>
                </ol>
            </div>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : $indexRoute }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i>{{ __('Kembali') }}</a>
        </div>

        <div class="row g-3">
            <div class="col-xl-7">
                <div class="alog-panel p-3 mb-3">
                    <div class="d-flex flex-wrap gap-1 mb-2">@include('activity-logs.partials.tags')</div>
                    <h2 class="fs-17 fw-bold mb-1">{{ $log->description }}</h2>
                    <div class="alog-meta">{{ $log->created_at?->setTimezone($tz)->translatedFormat('l, d F Y H:i:s') }} WIB</div>
                </div>

                @if ($diff)
                    <div class="alog-panel mb-3">
                        <div class="px-3 pt-3 pb-2 fw-bold">{{ __('Perubahan data') }}
                            <span class="alog-meta fw-normal">· {{ $log->subjectLabel() }} {{ $log->subject_id }}</span>
                        </div>
                        <table class="alog-diff">
                            <thead>
                                <tr><th>{{ __('Field') }}</th><th>{{ __('Sebelum') }}</th><th>{{ __('Sesudah') }}</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($diff as $field => [$old, $new])
                                    <tr>
                                        <td>{{ $field }}</td>
                                        <td class="{{ $log->event === 'created' ? '' : 'is-before' }}">
                                            @if ($log->event === 'created') <span class="is-null">—</span>
                                            @else @include('activity-logs.partials.value', ['value' => $old, 'limit' => 2000]) @endif
                                        </td>
                                        <td class="{{ $log->event === 'deleted' ? '' : 'is-after' }}">
                                            @if ($log->event === 'deleted') <span class="is-null">{{ __('dihapus') }}</span>
                                            @else @include('activity-logs.partials.value', ['value' => $new, 'limit' => 2000]) @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if (! empty($props['payload']))
                    <div class="alog-panel p-3 mb-3">
                        <div class="fw-bold mb-2">{{ __('Data yang dikirim') }} <span class="alog-meta fw-normal">· {{ __('nilai sensitif disamarkan') }}</span></div>
                        <pre class="alog-json">{{ json_encode($props['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif

                @if ($related->isNotEmpty())
                    <div class="alog-panel mb-3">
                        <div class="px-3 pt-3 pb-1 fw-bold">{{ __('Aktivitas lain dalam permintaan yang sama') }}</div>
                        <ul class="alog-timeline">
                            @foreach ($related as $r)
                                <li>
                                    <span class="alog-dot alog-dot--{{ $r->log_name }}"><i class="ri-{{ $r->log_name === 'data' ? 'database-2' : 'cursor' }}-line"></i></span>
                                    <a href="{{ $showRoute($r) }}" class="alog-desc">{{ $r->description }}</a>
                                    @include('activity-logs.partials.changes', ['log' => $r])
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="col-xl-5">
                <div class="alog-panel p-3">
                    <div class="fw-bold mb-2">{{ __('Konteks') }}</div>
                    <dl class="alog-kv">
                        @if ($admin)
                            <dt>{{ __('Pelaku') }}</dt>
                            <dd>{{ $log->causer_name ?? __('Tamu / tidak dikenal') }} @if ($log->causer_email)<span class="alog-meta">({{ $log->causer_email }} · {{ $log->causer_role }})</span>@endif</dd>
                        @endif
                        <dt>{{ __('Alamat IP') }}</dt><dd>{{ $log->ip_address ?? '-' }}</dd>
                        <dt>{{ __('Perangkat') }}</dt><dd>{{ $log->user_agent ?? '-' }}</dd>
                        <dt>{{ __('Method') }}</dt><dd>{{ $log->method ?? '-' }}</dd>
                        <dt>{{ __('URL') }}</dt><dd>{{ $log->url ?? '-' }}</dd>
                        @if ($admin)
                            <dt>{{ __('Route') }}</dt><dd>{{ $log->route_name ?? '-' }}</dd>
                        @endif
                        <dt>{{ __('Status') }}</dt><dd>{{ $log->status_code ?? '-' }}</dd>
                        <dt>{{ __('Durasi') }}</dt><dd>{{ $log->duration_ms !== null ? $log->duration_ms.' ms' : '-' }}</dd>
                        @if ($admin)
                            <dt>{{ __('Request ID') }}</dt>
                            <dd>@if ($log->request_id)<a href="{{ route('activity-logs.index', ['request_id' => $log->request_id]) }}">{{ $log->request_id }}</a>@else - @endif</dd>
                        @endif
                    </dl>
                    @php $extra = \Illuminate\Support\Arr::except($props, ['payload']); @endphp
                    @if ($admin && $extra)
                        <div class="fw-bold mt-3 mb-2">{{ __('Properti lain') }}</div>
                        <pre class="alog-json">{{ json_encode($extra, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
