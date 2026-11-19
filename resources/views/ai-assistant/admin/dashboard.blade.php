@extends('layouts.app')

@section('title', __('Monitoring AI Assistant'))

@section('content')
    @php
        $settings = app(\App\Services\AiAssistant\AiAssistantSettings::class);
        $isOn = $settings->bool('enabled') && $settings->bool('provider_enabled');
        $periods = ['today' => 'Hari ini', '7d' => '7 hari', '30d' => '30 hari', 'month' => 'Bulan ini', 'last_month' => 'Bulan lalu'];
        $num = fn ($v) => number_format((float) $v, 0, ',', '.');
        $successRate = $stats->total > 0 ? round($stats->success / $stats->total * 100) : 0;
        $maxTop = max(1, (int) ($topUsers->max('total') ?? 1));
        $maxCat = max(1, (int) ($categories->max('total') ?? 1));
    @endphp

    <div class="aiad">
        @include('ai-assistant.admin.partials.head', ['title' => __('Monitoring AI'), 'active' => 'dashboard'])

        <section class="aiad-hero">
            <div class="aiad-hero__row">
                <div class="aiad-hero__title">
                    <div class="aiad-hero__eyebrow">{{ __('Ruang Kendali AI Assistant') }}</div>
                    <h2>{{ __('Hari ini: :n pertanyaan dari :u pengguna', ['n' => $num($todayStats->total ?? 0), 'u' => $num($todayStats->users ?? 0)]) }}</h2>
                    <p>{{ __('Periode statistik: :from — :to', ['from' => \Carbon\Carbon::parse($from)->translatedFormat('d M Y'), 'to' => \Carbon\Carbon::parse($to)->translatedFormat('d M Y')]) }}</p>
                </div>
                <div class="aiad-hero__figure">
                    <strong>{{ $successRate }}%</strong>
                    <span>{{ __('tingkat keberhasilan periode ini') }}</span>
                </div>
                <div>
                    <span class="aiad-hero__status {{ $isOn ? 'is-on' : 'is-off' }}">
                        <i class="ri-checkbox-blank-circle-fill"></i>{{ $isOn ? __('AI Assistant aktif') : __('AI Assistant nonaktif') }}
                    </span>
                </div>
            </div>
        </section>

        <form class="aiad-filter" method="GET">
            <div>
                <span class="form-label d-block">{{ __('Periode') }}</span>
                <div class="aiad-seg">
                    @foreach ($periods as $key => $label)
                        <a href="{{ route('ai-assistant-logs.index', ['period' => $key]) }}" class="{{ $period === $key ? 'is-active' : '' }}">{{ __($label) }}</a>
                    @endforeach
                </div>
            </div>
            @include('ai-assistant.admin.partials.filter-dates')
            <div>
                <button class="btn btn-primary btn-sm px-3"><i class="ri-filter-3-line me-1"></i>{{ __('Terapkan') }}</button>
            </div>
        </form>

        <div class="aiad-stats">
            <div class="aiad-stat"><span class="aiad-stat__icon"><i class="ri-question-answer-line"></i></span><span class="aiad-stat__label">{{ __('Total pertanyaan') }}</span><span class="aiad-stat__value">{{ $num($stats->total) }}</span><span class="aiad-stat__sub">{{ __('hari ini: :n', ['n' => $num($todayStats->total ?? 0)]) }}</span></div>
            <div class="aiad-stat"><span class="aiad-stat__icon"><i class="ri-user-smile-line"></i></span><span class="aiad-stat__label">{{ __('Pengguna AI') }}</span><span class="aiad-stat__value">{{ $num($stats->users) }}</span><span class="aiad-stat__sub">{{ __('hari ini: :n', ['n' => $num($todayStats->users ?? 0)]) }}</span></div>
            <div class="aiad-stat"><span class="aiad-stat__icon"><i class="ri-chat-3-line"></i></span><span class="aiad-stat__label">{{ __('Total percakapan') }}</span><span class="aiad-stat__value">{{ $num($stats->conversations) }}</span><span class="aiad-stat__sub">{{ __('percakapan aktif di periode') }}</span></div>
            <div class="aiad-stat aiad-stat--good"><span class="aiad-stat__icon"><i class="ri-checkbox-circle-line"></i></span><span class="aiad-stat__label">{{ __('Berhasil') }}</span><span class="aiad-stat__value">{{ $num($stats->success) }}</span><span class="aiad-stat__sub">{{ __('kuota terpakai: :n', ['n' => $num($stats->quota_used)]) }}</span></div>
            <div class="aiad-stat aiad-stat--bad"><span class="aiad-stat__icon"><i class="ri-error-warning-line"></i></span><span class="aiad-stat__label">{{ __('Gagal') }}</span><span class="aiad-stat__value">{{ $num($stats->failed) }}</span><span class="aiad-stat__sub">{{ __('tidak memotong kuota') }}</span></div>
            <div class="aiad-stat aiad-stat--warn"><span class="aiad-stat__icon"><i class="ri-shield-cross-line"></i></span><span class="aiad-stat__label">{{ __('Ditolak firewall') }}</span><span class="aiad-stat__value">{{ $num($stats->blocked) }}</span><span class="aiad-stat__sub">{{ __('ditolak kuota habis: :n', ['n' => $num($stats->quota_rejected)]) }}</span></div>
            <div class="aiad-stat"><span class="aiad-stat__icon"><i class="ri-coins-line"></i></span><span class="aiad-stat__label">{{ __('Token digunakan') }}</span><span class="aiad-stat__value">{{ $num($stats->input_tokens + $stats->output_tokens) }}</span><span class="aiad-stat__sub">{{ __('in :i · out :o', ['i' => $num($stats->input_tokens), 'o' => $num($stats->output_tokens)]) }}</span></div>
            <div class="aiad-stat"><span class="aiad-stat__icon"><i class="ri-money-dollar-circle-line"></i></span><span class="aiad-stat__label">{{ __('Estimasi biaya') }}</span><span class="aiad-stat__value">${{ number_format((float) $stats->cost, 4) }}</span><span class="aiad-stat__sub">{{ __('estimasi dari tarif per token') }}</span></div>
            <div class="aiad-stat"><span class="aiad-stat__icon"><i class="ri-timer-flash-line"></i></span><span class="aiad-stat__label">{{ __('Rata-rata respons') }}</span><span class="aiad-stat__value">{{ $stats->avg_ms ? number_format($stats->avg_ms / 1000, 1, ',', '.').' dtk' : '-' }}</span><span class="aiad-stat__sub">{{ __('untuk jawaban berhasil') }}</span></div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="aiad-panel h-100">
                    <div class="aiad-panel__head">
                        <div>
                            <h3>{{ __('Tren harian') }}</h3>
                            <p>{{ __('Berhasil, gagal, dan ditolak per hari (zona waktu kuota)') }}</p>
                        </div>
                    </div>
                    <div class="aiad-panel__body"><div id="aiDailyChart" style="min-height: 290px"></div></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="aiad-panel h-100">
                    <div class="aiad-panel__head">
                        <div>
                            <h3>{{ __('Pengguna paling aktif') }}</h3>
                            <p>{{ __('Berdasarkan jumlah pertanyaan di periode') }}</p>
                        </div>
                        <a href="{{ route('ai-assistant-logs.users', ['sort' => 'period', 'from' => $from, 'to' => $to]) }}" class="small">{{ __('Semua') }} →</a>
                    </div>
                    <div class="aiad-panel__body">
                        @if ($topUsers->isEmpty())
                            <div class="aiad-empty"><i class="ri-user-search-line"></i>{{ __('Belum ada aktivitas.') }}</div>
                        @else
                            <ol class="aiad-rank">
                                @foreach ($topUsers as $i => $u)
                                    <li>
                                        <span class="aiad-rank__no">{{ $i + 1 }}</span>
                                        <div style="min-width:0">
                                            <a href="{{ route('ai-assistant-logs.users.show', $u->user_id) }}" class="aiad-rank__name d-block">{{ $u->user_name }}</a>
                                            <div class="aiad-rank__bar"><span style="width: {{ round($u->total / $maxTop * 100) }}%"></span></div>
                                        </div>
                                        <span class="aiad-rank__val">{{ $num($u->total) }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="aiad-panel">
                    <div class="aiad-panel__head">
                        <div>
                            <h3>{{ __('Kategori pertanyaan') }}</h3>
                            <p>{{ __('Termasuk kategori penolakan keamanan') }}</p>
                        </div>
                    </div>
                    <div class="aiad-panel__body">
                        @forelse ($categories as $c)
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <span class="small text-muted" style="width: 170px">{{ str_replace('_', ' ', $c->category) }}</span>
                                <div class="aiad-rank__bar flex-grow-1 mt-0" style="height:8px"><span style="width: {{ round($c->total / $maxCat * 100) }}%"></span></div>
                                <span class="fw-semibold small" style="width: 40px; text-align:right">{{ $num($c->total) }}</span>
                            </div>
                        @empty
                            <div class="aiad-empty"><i class="ri-pie-chart-line"></i>{{ __('Belum ada data.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend') }}/assets/libs/apexcharts/apexcharts.min.js"></script>
    <script>
        (function () {
            var el = document.getElementById('aiDailyChart');
            if (!el || typeof ApexCharts === 'undefined') return;
            var dark = document.documentElement.getAttribute('data-theme-mode') === 'dark';
            var s = @json($series);
            new ApexCharts(el, {
                chart: { type: 'bar', height: 290, stacked: true, toolbar: { show: false }, fontFamily: 'inherit', foreColor: dark ? '#94a3b8' : '#64748b' },
                series: [
                    { name: 'Berhasil', data: s.success },
                    { name: 'Ditolak', data: s.rejected },
                    { name: 'Gagal', data: s.failed },
                ],
                colors: ['#2b488b', '#e8a91c', '#dc2626'],
                plotOptions: { bar: { columnWidth: '52%', borderRadius: 4, borderRadiusApplication: 'end', borderRadiusWhenStacked: 'last' } },
                xaxis: { categories: s.labels, axisBorder: { show: false }, axisTicks: { show: false }, labels: { rotate: -45, hideOverlappingLabels: true } },
                yaxis: { labels: { formatter: function (v) { return Math.round(v); } } },
                grid: { borderColor: dark ? 'rgba(148,163,184,.12)' : '#eef0f5', strokeDashArray: 4 },
                dataLabels: { enabled: false },
                legend: { position: 'top', horizontalAlign: 'right', markers: { shape: 'square' } },
                tooltip: { theme: dark ? 'dark' : 'light' },
            }).render();
        })();
    </script>
@endpush
