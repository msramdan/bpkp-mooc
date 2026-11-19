@extends('layouts.app')

@section('title', __('Audit Admin AI'))

@section('content')
    @php $tz = app(\App\Services\AiAssistant\AiAssistantSettings::class)->timezone(); @endphp

    <div class="aiad">
        @include('ai-assistant.admin.partials.head', ['title' => __('Audit Admin'), 'active' => 'audit'])

        <form class="aiad-filter" method="GET">
            <div class="aiad-filter__grow">
                <label class="form-label" for="f-q">{{ __('Cari') }}</label>
                <input type="search" id="f-q" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('Nama admin, email, atau deskripsi') }}">
            </div>
            <div>
                <label class="form-label" for="f-action">{{ __('Aksi') }}</label>
                <select id="f-action" name="action" class="form-select form-select-sm">
                    <option value="">{{ __('Semua aksi') }}</option>
                    @foreach ($actions as $a)
                        <option value="{{ $a }}" @selected(request('action') === $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div><button class="btn btn-primary btn-sm px-3"><i class="ri-filter-3-line me-1"></i>{{ __('Terapkan') }}</button></div>
        </form>

        <div class="aiad-panel">
            <div class="aiad-panel__head pb-3">
                <div>
                    <h3>{{ __('Jejak aktivitas Super Admin') }}</h3>
                    <p>{{ __('Membuka monitoring, export log, dan perubahan pengaturan AI tercatat di sini.') }}</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table aiad-table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Waktu') }}</th>
                            <th>{{ __('Admin') }}</th>
                            <th>{{ __('Aksi') }}</th>
                            <th>{{ __('Deskripsi') }}</th>
                            <th>{{ __('Detail') }}</th>
                            <th>{{ __('IP') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="small text-nowrap">{{ $log->created_at?->setTimezone($tz)->format('d M Y H:i:s') }}</td>
                                <td><span class="fw-semibold">{{ $log->actor_name ?? '-' }}</span><div class="small text-muted">{{ $log->actor_email }}</div></td>
                                <td><code class="small">{{ $log->action }}</code></td>
                                <td class="small">{{ $log->description }}</td>
                                <td class="small" style="max-width: 320px">
                                    @if ($log->metadata)
                                        <details>
                                            <summary class="text-primary" style="cursor:pointer">{{ __('Lihat') }}</summary>
                                            <pre class="small mb-0 mt-1" style="white-space:pre-wrap">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="aiad-empty"><i class="ri-shield-user-line"></i>{{ __('Belum ada aktivitas admin.') }}</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="p-3 border-top">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
@endsection
