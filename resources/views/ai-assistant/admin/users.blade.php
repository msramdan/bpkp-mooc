@extends('layouts.app')

@section('title', __('Aktivitas AI per User'))

@section('content')
    @php
        $sortLink = function (string $key, string $label) use ($sort, $dir) {
            $nextDir = $sort === $key && $dir === 'desc' ? 'asc' : 'desc';
            $icon = $sort === $key ? ($dir === 'desc' ? ' ↓' : ' ↑') : '';
            $url = request()->fullUrlWithQuery(['sort' => $key, 'dir' => $nextDir, 'page' => null]);

            return '<a href="'.e($url).'" class="'.($sort === $key ? 'is-sorted' : '').'">'.e($label).$icon.'</a>';
        };
        $monitor = app(\App\Http\Controllers\AiAssistant\MonitoringController::class);
    @endphp

    <div class="aiad">
        @include('ai-assistant.admin.partials.head', ['title' => __('Aktivitas User'), 'active' => 'users'])

        <form class="aiad-filter" method="GET">
            <div class="aiad-filter__grow">
                <label class="form-label" for="f-q">{{ __('Cari user') }}</label>
                <input type="search" id="f-q" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('Nama, email, atau NIP') }}">
            </div>
            <div>
                <label class="form-label" for="f-status">{{ __('Status') }}</label>
                <select id="f-status" name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Semua status') }}</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            @include('ai-assistant.admin.partials.filter-dates')
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="dir" value="{{ $dir }}">
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm px-3"><i class="ri-filter-3-line me-1"></i>{{ __('Terapkan') }}</button>
                <a href="{{ route('ai-assistant-logs.users') }}" class="btn btn-light btn-sm">{{ __('Reset') }}</a>
                @can('ai assistant monitor export')
                    <a href="{{ route('ai-assistant-logs.users.export', request()->query()) }}" class="btn btn-outline-primary btn-sm"><i class="ri-download-2-line me-1"></i>{{ __('Export CSV') }}</a>
                @endcan
            </div>
        </form>

        <div class="aiad-panel">
            <div class="aiad-panel__head pb-3">
                <div>
                    <h3>{{ __('User AI Activity') }}</h3>
                    <p>
                        {{ __(':n user pernah memakai AI Assistant', ['n' => number_format($users->total(), 0, ',', '.')]) }}
                        · {{ __('kolom "Periode": :from s/d :to', ['from' => $from, 'to' => $to]) }}
                    </p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table aiad-table align-middle">
                    <thead>
                        <tr>
                            <th style="width:48px">{{ __('No.') }}</th>
                            <th>{!! $sortLink('name', __('User')) !!}</th>
                            <th class="text-center">{!! $sortLink('today', __('Hari ini')) !!}</th>
                            <th class="text-center">{!! $sortLink('month', __('Bulan ini')) !!}</th>
                            <th class="text-center">{!! $sortLink('period', __('Periode')) !!}</th>
                            <th>{{ __('Sisa kuota hari ini') }}</th>
                            <th class="text-center">{!! $sortLink('total', __('Total')) !!}</th>
                            <th>{!! $sortLink('last_used', __('Terakhir pakai')) !!}</th>
                            <th>{{ __('Status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $u)
                            @php
                                $remaining = max(0, $limit - (int) $u->today_success);
                                $statusLabel = $monitor->userStatusLabel($u, $limit);
                                $badge = match (true) {
                                    $limitEnabled && (int) $u->today_success >= $limit => 'rejected',
                                    (int) $u->today_count > 0 => 'success',
                                    default => 'muted',
                                };
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $users->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="aiad-user">
                                        <span class="aiad-user__avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($u->name, 0, 1)) }}</span>
                                        <div>
                                            <div class="aiad-user__name">{{ $u->name }}</div>
                                            <div class="aiad-user__email">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center fw-semibold">{{ (int) $u->today_count }}</td>
                                <td class="text-center">{{ (int) $u->month_count }}</td>
                                <td class="text-center">{{ (int) $u->period_count }}</td>
                                <td>
                                    @if ($limitEnabled)
                                        @if ($limit <= 12)
                                            <span class="aiad-pips" title="{{ $remaining }}/{{ $limit }}">
                                                @for ($i = 0; $i < $limit; $i++)
                                                    <i class="{{ $i >= $remaining ? 'is-used' : '' }}"></i>
                                                @endfor
                                            </span>
                                        @endif
                                        <span class="ms-1 small fw-semibold">{{ $remaining }}/{{ $limit }}</span>
                                    @else
                                        <span class="small text-muted">{{ __('Tidak dibatasi') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ (int) $u->total_count }}</td>
                                <td class="small">
                                    {{ $u->last_used_at ? \Carbon\Carbon::parse($u->last_used_at)->setTimezone(app(\App\Services\AiAssistant\AiAssistantSettings::class)->timezone())->format('d M Y H:i') : '-' }}
                                </td>
                                <td>
                                    <span class="aiad-badge aiad-badge--{{ $badge }}">{{ __($statusLabel) }}</span>
                                    @if ((int) $u->blocked_count > 0)
                                        <span class="aiad-badge aiad-badge--blocked mt-1" title="{{ __('Pernah ditolak firewall') }}">{{ (int) $u->blocked_count }} {{ __('ditolak') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('ai-assistant-logs.users.show', $u->id) }}" class="btn btn-sm btn-light"><i class="ri-chat-history-line me-1"></i>{{ __('Riwayat') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10"><div class="aiad-empty"><i class="ri-user-search-line"></i>{{ __('Tidak ada user yang cocok dengan filter.') }}</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="p-3 border-top">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
@endsection
