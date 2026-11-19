@extends('layouts.app')

@section('title', __('Log & Penolakan AI'))

@section('content')
    @php
        $tz = app(\App\Services\AiAssistant\AiAssistantSettings::class)->timezone();
        $statusLabels = ['success' => 'Berhasil', 'failed' => 'Gagal', 'rejected' => 'Ditolak', 'processing' => 'Diproses'];
    @endphp

    <div class="aiad">
        @include('ai-assistant.admin.partials.head', ['title' => __('Log & Penolakan'), 'active' => 'requests'])

        <form class="aiad-filter" method="GET">
            <div class="aiad-filter__grow">
                <label class="form-label" for="f-q">{{ __('Isi pertanyaan / jawaban') }}</label>
                <input type="search" id="f-q" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('Kata kunci…') }}">
            </div>
            <div>
                <label class="form-label" for="f-user">{{ __('User') }}</label>
                <input type="search" id="f-user" name="user" value="{{ request('user') }}" class="form-control form-control-sm" placeholder="{{ __('Nama / email') }}">
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
            <div>
                <label class="form-label" for="f-sec">{{ __('Keamanan') }}</label>
                <select id="f-sec" name="security" class="form-select form-select-sm">
                    <option value="">{{ __('Semua') }}</option>
                    <option value="blocked" @selected(request('security') === 'blocked')>{{ __('Diblokir firewall') }}</option>
                    <option value="redacted" @selected(request('security') === 'redacted')>{{ __('Output disensor') }}</option>
                    <option value="passed" @selected(request('security') === 'passed')>{{ __('Lolos') }}</option>
                </select>
            </div>
            <div>
                <label class="form-label" for="f-cat">{{ __('Kategori') }}</label>
                <select id="f-cat" name="category" class="form-select form-select-sm">
                    <option value="">{{ __('Semua') }}</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c }}" @selected(request('category') === $c)>{{ str_replace('_', ' ', $c) }}</option>
                    @endforeach
                </select>
            </div>
            @include('ai-assistant.admin.partials.filter-dates')
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm px-3"><i class="ri-filter-3-line me-1"></i>{{ __('Terapkan') }}</button>
                <a href="{{ route('ai-assistant-logs.requests', ['security' => 'blocked']) }}" class="btn btn-light btn-sm"><i class="ri-shield-cross-line me-1"></i>{{ __('Hanya penolakan') }}</a>
                @can('ai assistant monitor export')
                    <a href="{{ route('ai-assistant-logs.requests.export', request()->query()) }}" class="btn btn-outline-primary btn-sm"><i class="ri-download-2-line me-1"></i>{{ __('Export') }}</a>
                @endcan
            </div>
        </form>

        <div class="aiad-panel">
            <div class="table-responsive">
                <table class="table aiad-table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Waktu') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Pertanyaan') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Keamanan') }}</th>
                            <th>{{ __('Kategori') }}</th>
                            <th class="text-end">{{ __('Durasi') }}</th>
                            <th class="text-end">{{ __('Token') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="small text-nowrap">{{ $log->requested_at?->setTimezone($tz)->format('d M Y H:i') }}</td>
                                <td>
                                    @if ($log->user_id)
                                        <a href="{{ route('ai-assistant-logs.users.show', $log->user_id) }}" class="fw-semibold">{{ $log->user_name }}</a>
                                    @else
                                        <span class="fw-semibold">{{ $log->user_name ?? '-' }}</span>
                                    @endif
                                    <div class="small text-muted">{{ $log->user_email }}</div>
                                </td>
                                <td style="max-width: 340px">
                                    <div class="text-truncate" title="{{ $log->question }}">{{ $log->question }}</div>
                                    @if ($log->rejection_reason || $log->error_message)
                                        <div class="small text-danger text-truncate">{{ $log->rejection_reason ?? $log->error_message }}</div>
                                    @endif
                                </td>
                                <td>@include('ai-assistant.admin.partials.status-badge', ['value' => $log->status])</td>
                                <td>@include('ai-assistant.admin.partials.status-badge', ['value' => $log->security_status])</td>
                                <td class="small">{{ $log->category ? str_replace('_', ' ', $log->category) : '-' }}</td>
                                <td class="text-end small">{{ $log->duration_ms !== null ? number_format($log->duration_ms / 1000, 1, ',', '.').'s' : '-' }}</td>
                                <td class="text-end small">{{ $log->input_tokens !== null ? number_format($log->input_tokens + $log->output_tokens, 0, ',', '.') : '-' }}</td>
                                <td class="text-end"><a href="{{ route('ai-assistant-logs.requests.show', $log->id) }}" class="btn btn-sm btn-light">{{ __('Detail') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><div class="aiad-empty"><i class="ri-file-search-line"></i>{{ __('Tidak ada log yang cocok.') }}</div></td></tr>
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
