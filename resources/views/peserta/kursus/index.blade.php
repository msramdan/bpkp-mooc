@extends('layouts.app')

@section('title', __('Kursus Saya'))

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-kursus-grid.css" rel="stylesheet">
@endpush

@section('content')
    @php
        $berlangsung = $enrollments->where('status', 'Berlangsung')->count();
        $selesai = $enrollments->where('status', 'Selesai')->count();
        $rataProgress = $enrollments->isEmpty() ? 0 : (int) round($enrollments->avg('progress'));
    @endphp

    <div class="peserta-course-page">
        @include('peserta.partials.page-header', ['title' => __('Kursus Saya')])

        @if ($enrollments->isEmpty())
            <div class="peserta-course-empty">
                <div class="peserta-course-empty__icon">
                    <i class="bi bi-collection-play"></i>
                </div>
                <h2 class="peserta-course-empty__title">{{ __('Belum ada kursus terdaftar') }}</h2>
                <p class="peserta-course-empty__text">{{ __('Jelajahi katalog dan daftar kursus untuk mulai belajar.') }}</p>
                <a href="{{ route('peserta.katalog.index') }}" class="btn btn-primary btn-wave">
                    <i class="bi bi-grid me-1"></i>{{ __('Buka katalog') }}
                </a>
            </div>
        @else
            <div class="peserta-course-stats">
                <div class="peserta-course-stat peserta-course-stat--primary">
                    <span class="peserta-course-stat__icon"><i class="bi bi-journal-bookmark"></i></span>
                    <span class="peserta-course-stat__body">
                        <strong>{{ $enrollments->count() }}</strong>
                        <small>{{ __('Terdaftar') }}</small>
                    </span>
                </div>
                <div class="peserta-course-stat peserta-course-stat--info">
                    <span class="peserta-course-stat__icon"><i class="bi bi-play-circle"></i></span>
                    <span class="peserta-course-stat__body">
                        <strong>{{ $berlangsung }}</strong>
                        <small>{{ __('Berlangsung') }}</small>
                    </span>
                </div>
                <div class="peserta-course-stat peserta-course-stat--success">
                    <span class="peserta-course-stat__icon"><i class="bi bi-check-circle"></i></span>
                    <span class="peserta-course-stat__body">
                        <strong>{{ $selesai }}</strong>
                        <small>{{ __('Selesai') }}</small>
                    </span>
                </div>
                <div class="peserta-course-stat peserta-course-stat--accent">
                    <span class="peserta-course-stat__icon"><i class="bi bi-graph-up-arrow"></i></span>
                    <span class="peserta-course-stat__body">
                        <strong>{{ $rataProgress }}%</strong>
                        <small>{{ __('Rata progres') }}</small>
                    </span>
                </div>
            </div>

            <div class="peserta-course-panel" data-peserta-grid-filter data-grid="#pesertaKursusGrid"
                data-item-selector=".peserta-kursus-item" data-filter-key="status"
                data-total="{{ $enrollments->count() }}"
                data-no-results="#pesertaKursusNoResults"
                data-msg-visible="{{ __('Menampilkan :visible dari :total kursus') }}"
                data-msg-none="{{ __('Tidak ada kursus yang cocok dengan pencarian.') }}">
                @include('peserta.partials.course-search', [
                    'total' => $enrollments->count(),
                    'summary' => __(':count kursus terdaftar', ['count' => $enrollments->count()]),
                ])
                <div class="peserta-course-panel__bar">
                    <div class="peserta-course-filters" role="group" aria-label="{{ __('Filter kursus') }}">
                        <button type="button" class="peserta-course-filter active" data-filter-btn data-filter="all">
                            {{ __('Semua') }}
                        </button>
                        <button type="button" class="peserta-course-filter" data-filter-btn data-filter="Berlangsung">
                            {{ __('Berlangsung') }}
                        </button>
                        <button type="button" class="peserta-course-filter" data-filter-btn data-filter="Selesai">
                            {{ __('Selesai') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="peserta-kursus-grid" id="pesertaKursusGrid">
                @foreach ($enrollments as $enrollment)
                    @include('peserta.kursus._card', ['enrollment' => $enrollment])
                @endforeach
            </div>

            <div class="peserta-course-empty d-none" id="pesertaKursusNoResults">
                <div class="peserta-course-empty__icon peserta-course-empty__icon--muted">
                    <i class="bi bi-search"></i>
                </div>
                <h2 class="peserta-course-empty__title">{{ __('Tidak ada hasil') }}</h2>
                <p class="peserta-course-empty__text">{{ __('Tidak ada kursus yang cocok dengan pencarian.') }}</p>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('backend') }}/assets/js/peserta-course-grid-filter.js"></script>
@endpush
