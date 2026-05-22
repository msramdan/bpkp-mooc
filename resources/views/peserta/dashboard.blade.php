@extends('layouts.app')

@section('title', __('Beranda Peserta'))

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-dashboard.css" rel="stylesheet">
@endpush

@section('content')
    <div class="peserta-dash">
        @include('peserta.partials.page-header', ['title' => __('Beranda Peserta')])

        <div class="peserta-dash-hero">
            <div class="peserta-dash-hero__text">
                <h2>{{ __('Halo') }}, {{ auth()->user()->name }}</h2>
                <p>{{ __('Ringkasan pembelajaran hari ini') }}</p>
            </div>
            <div class="peserta-dash-hero__stats">
                <div class="peserta-dash-mini-stat peserta-dash-mini-stat--primary">
                    <i class="bi bi-play-circle"></i>
                    <span>{{ __('Aktif') }} <strong>{{ $stats['kursus_aktif'] }}</strong></span>
                </div>
                <div class="peserta-dash-mini-stat peserta-dash-mini-stat--success">
                    <i class="bi bi-check-circle"></i>
                    <span>{{ __('Selesai') }} <strong>{{ $stats['kursus_selesai'] }}</strong></span>
                </div>
                <div class="peserta-dash-mini-stat peserta-dash-mini-stat--warning">
                    <i class="bi bi-journal-text"></i>
                    <span>{{ __('Tugas') }} <strong>{{ $stats['tugas_pending'] }}</strong></span>
                </div>
                <div class="peserta-dash-mini-stat peserta-dash-mini-stat--info">
                    <i class="bi bi-patch-question"></i>
                    <span>{{ __('Ujian') }} <strong>{{ $stats['ujian_mendatang'] }}</strong></span>
                </div>
            </div>
            <div class="peserta-dash-progress-pill" style="--pct: {{ $rata_progress }}%;">
                <div class="peserta-dash-progress-pill__ring" aria-hidden="true"></div>
                <div>
                    <div class="peserta-dash-progress-pill__label">{{ __('Rata progres') }}</div>
                    <div class="peserta-dash-progress-pill__value">{{ $rata_progress }}%</div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-lg-8">
                <div class="peserta-dash-panel">
                    <div class="peserta-dash-panel__head">
                        <span class="peserta-dash-panel__title">{{ __('Progres per kursus') }}</span>
                    </div>
                    <div class="peserta-dash-panel__body peserta-dash-panel__body--chart">
                        <div id="chartProgressKursus" class="peserta-dash-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="peserta-dash-panel">
                    <div class="peserta-dash-panel__head">
                        <span class="peserta-dash-panel__title">{{ __('Status kursus') }}</span>
                    </div>
                    <div class="peserta-dash-panel__body peserta-dash-panel__body--chart">
                        <div id="chartStatusKursus" class="peserta-dash-chart"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2">
            <div class="col-lg-7">
                <div class="peserta-dash-panel">
                    <div class="peserta-dash-panel__head">
                        <span class="peserta-dash-panel__title">{{ __('Kursus saya') }}</span>
                        <a href="{{ route('peserta.kursus.index') }}" class="fs-12 text-primary">{{ __('Lihat semua') }}</a>
                    </div>
                    <div class="peserta-dash-panel__body py-2">
                        @foreach ($kursus as $enrollment)
                            @php $course = $enrollment->course; @endphp
                            <a href="{{ route('peserta.kursus.index') }}" class="peserta-dash-kursus-item">
                                <img src="{{ $course->thumbnail }}" alt="" class="peserta-dash-kursus-item__thumb"
                                    onerror="this.src='https://placehold.co/144x80/2b478b/ffffff?text=MOOC'">
                                <div class="peserta-dash-kursus-item__body">
                                    <p class="peserta-dash-kursus-item__title">{{ $course->judul }}</p>
                                    <div class="peserta-dash-kursus-item__bar">
                                        <span style="width:{{ $enrollment->progress }}%"></span>
                                    </div>
                                    <span class="peserta-dash-kursus-item__pct">{{ $enrollment->progress }}% · {{ $enrollment->status }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="peserta-dash-panel">
                    <div class="peserta-dash-panel__head">
                        <span class="peserta-dash-panel__title">{{ __('Jadwal terdekat') }}</span>
                        <a href="{{ route('peserta.jadwal.index') }}" class="fs-12 text-primary">{{ __('Kalender') }}</a>
                    </div>
                    <div class="peserta-dash-panel__body py-2">
                        @foreach ($jadwal as $item)
                            <div class="peserta-dash-jadwal-item">
                                <span class="peserta-dash-jadwal-item__dot"></span>
                                <div class="min-w-0">
                                    <span class="peserta-dash-jadwal-item__time">{{ $item['tanggal'] }} · {{ $item['waktu'] }}</span>
                                    <p class="peserta-dash-jadwal-item__title">{{ $item['kegiatan'] }}</p>
                                    <span class="peserta-dash-jadwal-item__sub">{{ $item['kursus'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend') }}/assets/libs/apexcharts/apexcharts.min.js"></script>
    <script>
        window.pesertaDashboardCharts = {
            progress: @json($chartProgress),
            status: @json($chartStatus),
            labels: {
                aktif: @json(__('Berlangsung')),
                selesai: @json(__('Selesai')),
                progres: @json(__('Progres (%)')),
            },
        };
    </script>
    <script src="{{ asset('backend') }}/assets/js/peserta-dashboard-charts.js"></script>
@endpush
