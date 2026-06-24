@extends('layouts.app')

@section('title', __('Beranda Peserta'))

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-dashboard.css" rel="stylesheet">
@endpush

@section('content')
    <div class="peserta-dash">
        @include('peserta.partials.page-header', ['title' => __('Beranda Peserta')])

        {{-- Welcome + ringkasan utama --}}
        <section class="peserta-dash-welcome">
            <div class="peserta-dash-welcome__copy">
                <span class="peserta-dash-welcome__eyebrow">{{ __('Portal pembelajaran') }}</span>
                <h2 class="peserta-dash-welcome__title">{{ __('Halo') }}, {{ auth()->user()->name }}</h2>
                <p class="peserta-dash-welcome__sub">
                    {{ __('Pantau progres kursus, lanjutkan materi, dan unduh sertifikat Anda.') }}
                </p>
                @if ($lanjutkan)
                    <a href="{{ route('peserta.kursus.show', $lanjutkan->course) }}"
                        class="btn btn-primary btn-wave btn-sm peserta-dash-welcome__cta">
                        {{ $lanjutkan->status === 'Selesai' ? __('Buka kursus') : __('Lanjutkan belajar') }}
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <p class="peserta-dash-welcome__hint mb-0">
                        {{ \Illuminate\Support\Str::limit($lanjutkan->course->judul, 48) }}
                        · {{ $lanjutkan->progress }}%
                    </p>
                @endif
            </div>
            <div class="peserta-dash-welcome__meter" style="--pct: {{ $rata_progress }}%;">
                <div class="peserta-dash-welcome__ring" aria-hidden="true"></div>
                <div class="peserta-dash-welcome__meter-text">
                    <strong>{{ $rata_progress }}%</strong>
                    <small>{{ __('Rata progres') }}</small>
                </div>
            </div>
        </section>

        {{-- Stat tiles --}}
        <div class="peserta-dash-metrics">
            <div class="peserta-dash-metric">
                <span class="peserta-dash-metric__icon peserta-dash-metric__icon--primary">
                    <i class="bi bi-journal-bookmark"></i>
                </span>
                <div class="peserta-dash-metric__body">
                    <strong>{{ $stats['terdaftar'] }}</strong>
                    <span>{{ __('Kursus terdaftar') }}</span>
                </div>
            </div>
            <div class="peserta-dash-metric">
                <span class="peserta-dash-metric__icon peserta-dash-metric__icon--info">
                    <i class="bi bi-play-circle"></i>
                </span>
                <div class="peserta-dash-metric__body">
                    <strong>{{ $stats['kursus_aktif'] }}</strong>
                    <span>{{ __('Sedang berjalan') }}</span>
                </div>
            </div>
            <div class="peserta-dash-metric">
                <span class="peserta-dash-metric__icon peserta-dash-metric__icon--success">
                    <i class="bi bi-check-circle"></i>
                </span>
                <div class="peserta-dash-metric__body">
                    <strong>{{ $stats['kursus_selesai'] }}</strong>
                    <span>{{ __('Selesai') }}</span>
                </div>
            </div>
            <div class="peserta-dash-metric">
                <span class="peserta-dash-metric__icon peserta-dash-metric__icon--accent">
                    <i class="bi bi-award"></i>
                </span>
                <div class="peserta-dash-metric__body">
                    <strong>{{ $stats['sertifikat'] }}</strong>
                    <span>{{ __('Sertifikat') }}</span>
                </div>
            </div>
        </div>

        {{-- Analytics --}}
        <div class="row g-2 mb-2">
            <div class="col-lg-8">
                <div class="peserta-dash-panel">
                    <div class="peserta-dash-panel__head">
                        <span class="peserta-dash-panel__title">{{ __('Progres per kursus') }}</span>
                    </div>
                    <div class="peserta-dash-panel__body peserta-dash-panel__body--chart">
                        @if (count($chartProgress['labels']))
                            <div id="chartProgressKursus" class="peserta-dash-chart"></div>
                        @else
                            <p class="peserta-dash-panel__empty">{{ __('Belum ada data progres.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="peserta-dash-panel">
                    <div class="peserta-dash-panel__head">
                        <span class="peserta-dash-panel__title">{{ __('Komposisi status') }}</span>
                    </div>
                    <div class="peserta-dash-panel__body peserta-dash-panel__body--chart">
                        <div id="chartStatusKursus" class="peserta-dash-chart"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kursus + sertifikat --}}
        <div class="row g-2 peserta-dash-bottom">
            <div class="col-lg-7">
                <div class="peserta-dash-panel peserta-dash-panel--courses">
                    <div class="peserta-dash-panel__head">
                        <span class="peserta-dash-panel__title">{{ __('Lanjutkan belajar') }}</span>
                        <a href="{{ route('peserta.kursus.index') }}" class="peserta-dash-panel__link">{{ __('Semua kursus') }}</a>
                    </div>
                    <div class="peserta-dash-panel__body">
                        @forelse ($kursus as $enrollment)
                            @php
                                $course = $enrollment->course;
                                $statusClass = match ($enrollment->status) {
                                    'Selesai' => 'success',
                                    'Berlangsung' => 'primary',
                                    default => 'secondary',
                                };
                            @endphp
                            <a href="{{ route('peserta.kursus.show', $course) }}" class="peserta-dash-course">
                                <div class="peserta-dash-course__media">
                                    <x-course-thumbnail :course="$course" class="peserta-dash-course__thumb" />
                                    <span class="peserta-dash-course__status peserta-dash-course__status--{{ $statusClass }}">
                                        {{ $enrollment->status }}
                                    </span>
                                </div>
                                <div class="peserta-dash-course__body">
                                    <span class="peserta-dash-course__category">{{ $course->kategori }}</span>
                                    <h3 class="peserta-dash-course__title">{{ $course->judul }}</h3>
                                    <p class="peserta-dash-course__meta">
                                        {{ __('Modul') }} {{ $enrollment->modulLabel() }}
                                        · {{ $course->durasi_jam }} {{ __('jam') }}
                                    </p>
                                    <div class="peserta-dash-course__track" aria-hidden="true">
                                        <span style="width: {{ $enrollment->progress }}%"></span>
                                    </div>
                                </div>
                                <div class="peserta-dash-course__ring" style="--pct: {{ $enrollment->progress }}%;">
                                    <span>{{ $enrollment->progress }}%</span>
                                </div>
                            </a>
                        @empty
                            <div class="peserta-dash-panel__empty-state">
                                <i class="bi bi-collection-play"></i>
                                <p>{{ __('Belum ada kursus. Jelajahi katalog untuk mulai belajar.') }}</p>
                                <a href="{{ route('peserta.katalog.index') }}" class="btn btn-primary btn-sm btn-wave">
                                    {{ __('Buka katalog') }}
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="peserta-dash-panel peserta-dash-panel--certs">
                    <div class="peserta-dash-panel__head">
                        <span class="peserta-dash-panel__title">{{ __('Sertifikat terbaru') }}</span>
                        <a href="{{ route('peserta.sertifikat.index') }}" class="peserta-dash-panel__link">{{ __('Semua') }}</a>
                    </div>
                    <div class="peserta-dash-panel__body">
                        @forelse ($sertifikatTerbaru as $certificate)
                            <a href="{{ route('peserta.sertifikat.show', $certificate) }}" class="peserta-dash-cert" target="_blank">
                                <span class="peserta-dash-cert__badge">
                                    <i class="bi bi-award"></i>
                                </span>
                                <span class="peserta-dash-cert__body">
                                    <strong>{{ \Illuminate\Support\Str::limit($certificate->course->judul, 42) }}</strong>
                                    <small>{{ $certificate->nomor }}</small>
                                    <small>{{ $certificate->issued_at?->format('d M Y') }}</small>
                                </span>
                                <i class="bi bi-chevron-right peserta-dash-cert__arrow"></i>
                            </a>
                        @empty
                            <div class="peserta-dash-panel__empty-state peserta-dash-panel__empty-state--compact">
                                <i class="bi bi-award"></i>
                                <p>{{ __('Selesaikan kursus untuk mendapatkan sertifikat.') }}</p>
                            </div>
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
