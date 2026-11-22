@extends('layouts.app')

@section('title', __('Log Aktivitas'))

@section('content')
    @include('activity-logs.partials.head')
    @php
        $tz = \App\Http\Controllers\ActivityLogController::TZ;
        $num = fn ($v) => number_format((float) $v, 0, ',', '.');
    @endphp

    <div class="alog">
        <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Log Aktivitas') }}</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item">{{ __('Utilitas') }}</li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Log Aktivitas') }}</li>
                </ol>
            </div>
        </div>

        <section class="alog-hero">
            <h2>
                @if ($subjectUser)
                    {{ __('Jejak aktivitas :name', ['name' => $subjectUser->name]) }}
                @else
                    {{ __('Jejak aktivitas seluruh pengguna') }}
                @endif
            </h2>
            <p>{{ __('Halaman yang dibuka, aksi yang dilakukan, login/logout, dan setiap perubahan data beserta nilai sebelum & sesudah.') }}</p>
            <div class="alog-hero__stats">
                <div class="alog-hero__stat"><strong>{{ $num($stats->total) }}</strong><span>{{ __('aktivitas hari ini') }}</span></div>
                <div class="alog-hero__stat"><strong>{{ $num($stats->users) }}</strong><span>{{ __('user aktif hari ini') }}</span></div>
                <div class="alog-hero__stat"><strong>{{ $num($stats->data_changes) }}</strong><span>{{ __('perubahan data hari ini') }}</span></div>
                <div class="alog-hero__stat {{ $stats->failed_logins > 0 ? 'is-warn' : '' }}"><strong>{{ $num($stats->failed_logins) }}</strong><span>{{ __('login gagal hari ini') }}</span></div>
                <div class="alog-hero__stat {{ $stats->server_errors > 0 ? 'is-warn' : '' }}"><strong>{{ $num($stats->server_errors) }}</strong><span>{{ __('error server hari ini') }}</span></div>
            </div>
        </section>

        <form class="alog-filter" method="GET">
            @if (request('user_id'))
                <input type="hidden" name="user_id" value="{{ request('user_id') }}">
            @endif
            <div class="alog-filter__grow">
                <label class="form-label" for="f-q">{{ __('Aktivitas / URL') }}</label>
                <input type="search" id="f-q" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('mis. mengubah kursus, /peserta/...') }}">
            </div>
            @unless (request('user_id'))
                <div>
                    <label class="form-label" for="f-user">{{ __('User') }}</label>
                    <input type="search" id="f-user" name="user" value="{{ request('user') }}" class="form-control form-control-sm" placeholder="{{ __('Nama / email') }}">
                </div>
            @endunless
            <div>
                <label class="form-label" for="f-log">{{ __('Jenis') }}</label>
                <select id="f-log" name="log" class="form-select form-select-sm">
                    <option value="">{{ __('Semua') }}</option>
                    @foreach ($logNames as $key => $label)
                        <option value="{{ $key }}" @selected(request('log') === $key)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="f-event">{{ __('Event') }}</label>
                <select id="f-event" name="event" class="form-select form-select-sm">
                    <option value="">{{ __('Semua') }}</option>
                    @foreach ($events as $ev)
                        <option value="{{ $ev }}" @selected(request('event') === $ev)>{{ __(\App\Support\Activity\ActivityDescriber::eventLabel($ev)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="f-subject">{{ __('Data') }}</label>
                <select id="f-subject" name="subject_type" class="form-select form-select-sm">
                    <option value="">{{ __('Semua') }}</option>
                    @foreach ($models as $class => $label)
                        <option value="{{ $class }}" @selected(request('subject_type') === $class)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="f-status">{{ __('Status') }}</label>
                <select id="f-status" name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Semua') }}</option>
                    <option value="ok" @selected(request('status') === 'ok')>{{ __('Berhasil (2xx/3xx)') }}</option>
                    <option value="4xx" @selected(request('status') === '4xx')>{{ __('Ditolak (4xx)') }}</option>
                    <option value="5xx" @selected(request('status') === '5xx')>{{ __('Error (5xx)') }}</option>
                </select>
            </div>
            <div>
                <label class="form-label" for="f-ip">{{ __('IP') }}</label>
                <input type="text" id="f-ip" name="ip" value="{{ request('ip') }}" class="form-control form-control-sm" style="min-width:120px">
            </div>
            <div>
                <label class="form-label" for="f-from">{{ __('Dari') }}</label>
                <input type="date" id="f-from" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
            </div>
            <div>
                <label class="form-label" for="f-to">{{ __('Sampai') }}</label>
                <input type="date" id="f-to" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary btn-sm px-3"><i class="ri-filter-3-line me-1"></i>{{ __('Terapkan') }}</button>
                <a href="{{ route('activity-logs.index', request()->only('user_id')) }}" class="btn btn-light btn-sm">{{ __('Reset') }}</a>
                @can('activity log export')
                    <a href="{{ route('activity-logs.export', request()->query()) }}" class="btn btn-outline-primary btn-sm"><i class="ri-download-2-line me-1"></i>{{ __('Export CSV') }}</a>
                @endcan
            </div>
        </form>

        <div class="alog-panel">
            <div class="table-responsive">
                <table class="table alog-table">
                    <thead>
                        <tr>
                            <th>{{ __('Waktu (WIB)') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Aktivitas') }}</th>
                            <th>{{ __('Asal') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="alog-time">
                                    <b>{{ $log->created_at?->setTimezone($tz)->format('H:i:s') }}</b>
                                    {{ $log->created_at?->setTimezone($tz)->translatedFormat('d M Y') }}
                                </td>
                                <td class="alog-who">
                                    @if ($log->causer_id)
                                        <a href="{{ route('activity-logs.index', ['user_id' => $log->causer_id]) }}"><b>{{ $log->causer_name ?? __('User #:id', ['id' => $log->causer_id]) }}</b></a>
                                        <div class="alog-meta">{{ $log->causer_email }} · {{ $log->causer_role }}</div>
                                    @else
                                        <b class="text-muted">{{ __('Tamu / tidak dikenal') }}</b>
                                        @if ($log->getProperty('email'))
                                            <div class="alog-meta">{{ $log->getProperty('email') }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1 mb-1">@include('activity-logs.partials.tags')</div>
                                    <div class="alog-desc">{{ $log->description }}</div>
                                    @if ($log->url)
                                        <div class="alog-meta">{{ $log->method }} {{ \Illuminate\Support\Str::limit($log->url, 90) }}@if ($log->duration_ms !== null) · {{ $log->duration_ms }} ms @endif</div>
                                    @endif
                                    @include('activity-logs.partials.changes')
                                </td>
                                <td class="alog-meta" style="min-width:140px">
                                    {{ $log->ip_address ?? '-' }}
                                    <div>{{ \Illuminate\Support\Str::limit((string) $log->user_agent, 40) }}</div>
                                </td>
                                <td class="text-end"><a href="{{ route('activity-logs.show', $log) }}" class="btn btn-sm btn-light">{{ __('Detail') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="alog-empty"><i class="ri-history-line"></i>{{ __('Tidak ada aktivitas yang cocok dengan filter.') }}</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="p-3 border-top">{{ $logs->links() }}</div>
            @endif
        </div>

        @can('activity log manage')
            <form method="POST" action="{{ route('activity-logs.prune') }}" class="mt-3 d-flex align-items-center gap-2 flex-wrap small text-muted"
                onsubmit="return confirm(@js(__('Hapus permanen log aktivitas yang lebih lama dari :d hari?', ['d' => config('activitylog.clean_after_days')])))">
                @csrf
                <input class="form-check-input m-0" type="checkbox" name="confirm" value="1" id="alog-confirm" required>
                <label for="alog-confirm">{{ __('Bersihkan log lebih lama dari :d hari (tidak bisa dibatalkan)', ['d' => config('activitylog.clean_after_days')]) }}</label>
                <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-6-line me-1"></i>{{ __('Bersihkan') }}</button>
            </form>
        @endcan
    </div>
@endsection
