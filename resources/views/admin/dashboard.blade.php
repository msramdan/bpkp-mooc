@extends('layouts.app')

@section('title', __('Dashboard'))

@push('css')
    <link href="{{ asset('backend') }}/assets/css/admin-dashboard.css" rel="stylesheet">
@endpush

@section('content')
    <div class="admin-dash">
        <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Dashboard') }}</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Ringkasan MOOC') }}</li>
                </ol>
            </div>
            @can('course create')
                <a href="{{ route('courses.create') }}" class="btn btn-primary btn-sm btn-wave">
                    <i class="bi bi-plus-lg me-1"></i>{{ __('Tambah kursus') }}
                </a>
            @endcan
        </div>

        <section class="admin-dash-welcome">
            <div class="admin-dash-welcome__copy">
                <span class="admin-dash-welcome__eyebrow">{{ __('Panel administrator') }}</span>
                <h2 class="admin-dash-welcome__title">{{ __('Ringkasan platform MOOC') }}</h2>
                <p class="admin-dash-welcome__sub">
                    {{ __('Pantau kursus, pendaftaran peserta, dan progres penyelesaian dalam satu tampilan.') }}
                </p>
                <div class="admin-dash-welcome__chips">
                    <span class="admin-dash-chip">
                        <i class="bi bi-journal-check"></i>{{ $stats['published'] }}/{{ $stats['courses'] }} {{ __('publik') }}
                    </span>
                    <span class="admin-dash-chip">
                        <i class="bi bi-people"></i>{{ number_format($stats['peserta']) }} {{ __('peserta') }}
                    </span>
                    <span class="admin-dash-chip">
                        <i class="bi bi-award"></i>{{ number_format($stats['certificates']) }} {{ __('sertifikat') }}
                    </span>
                </div>
            </div>
            <div class="admin-dash-welcome__meters">
                <div class="admin-dash-meter" style="--pct: {{ $publishRate }}%;">
                    <div class="admin-dash-meter__ring" aria-hidden="true"></div>
                    <div class="admin-dash-meter__text">
                        <strong>{{ $publishRate }}%</strong>
                        <small>{{ __('Kursus publik') }}</small>
                    </div>
                </div>
                <div class="admin-dash-meter admin-dash-meter--success" style="--pct: {{ $completionRate }}%;">
                    <div class="admin-dash-meter__ring" aria-hidden="true"></div>
                    <div class="admin-dash-meter__text">
                        <strong>{{ $completionRate }}%</strong>
                        <small>{{ __('Selesai') }}</small>
                    </div>
                </div>
            </div>
        </section>

        <div class="admin-dash-metrics">
            <div class="admin-dash-metric">
                <span class="admin-dash-metric__icon admin-dash-metric__icon--primary">
                    <i class="bi bi-collection"></i>
                </span>
                <div class="admin-dash-metric__body">
                    <strong>{{ number_format($stats['courses']) }}</strong>
                    <span>{{ __('Total kursus') }}</span>
                </div>
            </div>
            <div class="admin-dash-metric">
                <span class="admin-dash-metric__icon admin-dash-metric__icon--success">
                    <i class="bi bi-broadcast"></i>
                </span>
                <div class="admin-dash-metric__body">
                    <strong>{{ number_format($stats['published']) }}</strong>
                    <span>{{ __('Dipublikasikan') }}</span>
                </div>
            </div>
            <div class="admin-dash-metric">
                <span class="admin-dash-metric__icon admin-dash-metric__icon--info">
                    <i class="bi bi-person-check"></i>
                </span>
                <div class="admin-dash-metric__body">
                    <strong>{{ number_format($stats['enrollments']) }}</strong>
                    <span>{{ __('Pendaftaran') }}</span>
                </div>
            </div>
            <div class="admin-dash-metric">
                <span class="admin-dash-metric__icon admin-dash-metric__icon--accent">
                    <i class="bi bi-people"></i>
                </span>
                <div class="admin-dash-metric__body">
                    <strong>{{ number_format($stats['peserta']) }}</strong>
                    <span>{{ __('Peserta') }}</span>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-lg-4">
                <div class="admin-dash-panel">
                    <div class="admin-dash-panel__head">
                        <span class="admin-dash-panel__title">{{ __('Status publikasi') }}</span>
                    </div>
                    <div class="admin-dash-panel__body admin-dash-panel__body--chart">
                        <div id="chartAdminPublish" class="admin-dash-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="admin-dash-panel">
                    <div class="admin-dash-panel__head">
                        <span class="admin-dash-panel__title">{{ __('Progres peserta') }}</span>
                    </div>
                    <div class="admin-dash-panel__body admin-dash-panel__body--chart">
                        <div id="chartAdminEnrollment" class="admin-dash-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="admin-dash-panel">
                    <div class="admin-dash-panel__head">
                        <span class="admin-dash-panel__title">{{ __('Top kursus') }}</span>
                    </div>
                    <div class="admin-dash-panel__body admin-dash-panel__body--chart">
                        @if (count($chartTopCourses['labels']))
                            <div id="chartAdminTopCourses" class="admin-dash-chart"></div>
                        @else
                            <p class="admin-dash-panel__empty">{{ __('Belum ada data kursus.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-dash-panel">
            <div class="admin-dash-panel__head">
                <span class="admin-dash-panel__title">{{ __('Kursus terbaru') }}</span>
                @can('course view')
                    <a href="{{ route('courses.index') }}" class="admin-dash-panel__link">{{ __('Kelola kursus') }}</a>
                @endcan
            </div>
            <div class="admin-dash-panel__body admin-dash-panel__body--courses">
                @forelse ($recentCourses as $course)
                    <a href="{{ route('courses.show', $course) }}" class="admin-dash-course-row">
                        <div class="admin-dash-course-row__media">
                            <x-course-thumbnail :course="$course" class="admin-dash-course-row__thumb" />
                        </div>
                        <div class="admin-dash-course-row__body">
                            <span class="admin-dash-course-row__code">{{ $course->kode }}</span>
                            <h3 class="admin-dash-course-row__title">{{ $course->judul }}</h3>
                            <p class="admin-dash-course-row__meta">
                                {{ $course->kategori }}
                                · {{ $course->modul_total }} {{ __('modul') }}
                                · {{ number_format($course->enrollments_count) }} {{ __('peserta') }}
                            </p>
                        </div>
                        <div class="admin-dash-course-row__aside">
                            @if ($course->is_published)
                                <span class="admin-dash-course-row__badge admin-dash-course-row__badge--success">
                                    {{ __('Dipublikasikan') }}
                                </span>
                            @else
                                <span class="admin-dash-course-row__badge admin-dash-course-row__badge--muted">
                                    {{ __('Draft') }}
                                </span>
                            @endif
                            <i class="bi bi-chevron-right admin-dash-course-row__arrow"></i>
                        </div>
                    </a>
                @empty
                    <div class="admin-dash-panel__empty-state">
                        <i class="bi bi-journal-plus"></i>
                        <p>{{ __('Belum ada kursus. Mulai dengan menambahkan kursus pertama.') }}</p>
                        @can('course create')
                            <a href="{{ route('courses.create') }}" class="btn btn-primary btn-sm btn-wave">
                                {{ __('Tambah kursus') }}
                            </a>
                        @endcan
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend') }}/assets/libs/apexcharts/apexcharts.min.js"></script>
    <script>
        window.adminDashboardCharts = {
            publish: @json($chartPublish),
            enrollment: @json($chartEnrollment),
            topCourses: @json($chartTopCourses),
            labels: {
                published: @json(__('Dipublikasikan')),
                draft: @json(__('Draft')),
                berlangsung: @json(__('Berlangsung')),
                selesai: @json(__('Selesai')),
                peserta: @json(__('Peserta')),
            },
        };
    </script>
    <script src="{{ asset('backend') }}/assets/js/admin-dashboard-charts.js"></script>
@endpush
