@extends('layouts.app')

@section('title', __('Aktivitas Saya'))

@section('content')
    @include('activity-logs.partials.head')
    @php
        $tz = \App\Http\Controllers\ActivityLogController::TZ;
        $home = auth()->user()->hasRole('peserta') ? route('peserta.dashboard') : route('dashboard');
        $lastDay = null;
    @endphp

    <div class="alog">
        <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Aktivitas Saya') }}</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ $home }}">{{ __('Beranda') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Aktivitas Saya') }}</li>
                </ol>
            </div>
        </div>

        <section class="alog-hero">
            <h2>{{ __('Riwayat aktivitas akun Anda') }}</h2>
            <p>{{ __('Hanya Anda (dan admin) yang dapat melihat riwayat ini. Periksa jika ada login atau aktivitas yang tidak Anda kenali.') }}</p>
            <div class="alog-hero__stats">
                <div class="alog-hero__stat">
                    <strong>{{ $lastLogin ? $lastLogin->created_at->setTimezone($tz)->translatedFormat('d M, H:i') : '-' }}</strong>
                    <span>{{ __('login sebelumnya') }}@if ($lastLogin) · {{ $lastLogin->ip_address }}@endif</span>
                </div>
                <div class="alog-hero__stat {{ $failedLogins > 0 ? 'is-warn' : '' }}">
                    <strong>{{ $failedLogins }}</strong>
                    <span>{{ __('login gagal (30 hari)') }}</span>
                </div>
            </div>
        </section>

        <form class="alog-filter" method="GET">
            <div class="alog-filter__grow">
                <label class="form-label" for="m-q">{{ __('Cari aktivitas') }}</label>
                <input type="search" id="m-q" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('mis. materi, sertifikat, login') }}">
            </div>
            <div>
                <label class="form-label" for="m-log">{{ __('Jenis') }}</label>
                <select id="m-log" name="log" class="form-select form-select-sm">
                    <option value="">{{ __('Semua') }}</option>
                    @foreach ($logNames as $key => $label)
                        <option value="{{ $key }}" @selected(request('log') === $key)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="m-from">{{ __('Dari') }}</label>
                <input type="date" id="m-from" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
            </div>
            <div>
                <label class="form-label" for="m-to">{{ __('Sampai') }}</label>
                <input type="date" id="m-to" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm px-3"><i class="ri-filter-3-line me-1"></i>{{ __('Terapkan') }}</button>
                <a href="{{ route('activity.mine') }}" class="btn btn-light btn-sm">{{ __('Reset') }}</a>
            </div>
        </form>

        <div class="alog-panel">
            @if ($logs->isEmpty())
                <div class="alog-empty"><i class="ri-history-line"></i>{{ __('Belum ada aktivitas yang cocok.') }}</div>
            @else
                <ul class="alog-timeline">
                    @foreach ($logs as $log)
                        @php
                            $local = $log->created_at?->setTimezone($tz);
                            $day = $local?->translatedFormat('l, d F Y');
                            $bad = in_array($log->event, ['failed', 'lockout'], true) || ($log->status_code ?? 0) >= 400;
                            $icon = match ($log->log_name) { 'auth' => 'ri-login-circle-line', 'data' => 'ri-database-2-line', default => 'ri-cursor-line' };
                        @endphp
                        @if ($day !== $lastDay)
                            <li class="alog-day">{{ $day }}</li>
                            @php $lastDay = $day; @endphp
                        @endif
                        <li>
                            <span class="alog-dot alog-dot--{{ $bad ? 'bad' : $log->log_name }}"><i class="{{ $icon }}"></i></span>
                            <div class="d-flex justify-content-between gap-2 flex-wrap">
                                <a href="{{ route('activity.mine.show', $log) }}" class="alog-desc">{{ $log->description }}</a>
                                <span class="alog-time">{{ $local?->format('H:i:s') }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mt-1">@include('activity-logs.partials.tags')</div>
                            <div class="alog-meta mt-1">{{ $log->ip_address }} · {{ \Illuminate\Support\Str::limit((string) $log->user_agent, 60) }}</div>
                            @include('activity-logs.partials.changes')
                        </li>
                    @endforeach
                </ul>
            @endif
            @if ($logs->hasPages())
                <div class="p-3 border-top">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
@endsection
