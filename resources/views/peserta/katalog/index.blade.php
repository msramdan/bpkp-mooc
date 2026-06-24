@extends('layouts.app')

@section('title', __('Katalog Kursus'))

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-kursus-grid.css" rel="stylesheet">
@endpush

@section('content')
    <div class="peserta-course-page">
        @include('peserta.partials.page-header', ['title' => __('Katalog Kursus')])

        @if ($courses->isEmpty())
            <div class="peserta-course-empty">
                <div class="peserta-course-empty__icon">
                    <i class="bi bi-grid"></i>
                </div>
                <h2 class="peserta-course-empty__title">{{ __('Belum ada kursus di katalog') }}</h2>
                <p class="peserta-course-empty__text">{{ __('Kursus baru akan muncul di sini setelah dipublikasikan.') }}</p>
            </div>
        @else
            <div class="peserta-course-panel" data-peserta-grid-filter data-grid="#pesertaKatalogGrid"
                data-item-selector=".peserta-katalog-item" data-filter-key="kategori"
                data-total="{{ $courses->count() }}"
                data-no-results="#pesertaKatalogNoResults"
                data-msg-visible="{{ __('Menampilkan :visible dari :total kursus') }}"
                data-msg-none="{{ __('Tidak ada kursus yang cocok dengan pencarian.') }}">
                @include('peserta.partials.course-search', [
                    'total' => $courses->count(),
                    'summary' => __(':count kursus tersedia', ['count' => $courses->count()]),
                ])
                <div class="peserta-course-panel__bar">
                    <div class="peserta-course-filters" role="group" aria-label="{{ __('Filter kategori') }}">
                        <button type="button" class="peserta-course-filter active" data-filter-btn data-filter="all">
                            {{ __('Semua') }}
                        </button>
                        @foreach ($kategoris as $kategori)
                            <button type="button" class="peserta-course-filter" data-filter-btn
                                data-filter="{{ $kategori }}">{{ $kategori }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="peserta-kursus-grid" id="pesertaKatalogGrid">
                @foreach ($courses as $course)
                    @include('peserta.katalog._card', [
                        'course' => $course,
                        'enrolledIds' => $enrolledIds,
                    ])
                @endforeach
            </div>

            <div class="peserta-course-empty d-none" id="pesertaKatalogNoResults">
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
