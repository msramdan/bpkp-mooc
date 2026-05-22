@extends('layouts.app')

@section('title', __('Katalog Kursus'))

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-kursus-grid.css" rel="stylesheet">
@endpush

@section('content')
    @include('peserta.partials.page-header', ['title' => __('Katalog Kursus')])

    @if ($courses->isEmpty())
        <div class="peserta-kursus-empty">
            <i class="bi bi-grid fs-1 d-block mb-2 opacity-50"></i>
            <p class="mb-0">{{ __('Belum ada kursus di katalog.') }}</p>
        </div>
    @else
        <div class="peserta-grid-filter-wrap" data-peserta-grid-filter data-grid="#pesertaKatalogGrid"
            data-item-selector=".peserta-katalog-item" data-filter-key="kategori"
            data-total="{{ $courses->count() }}"
            data-no-results="#pesertaKatalogNoResults"
            data-msg-visible="{{ __('Menampilkan :visible dari :total kursus') }}"
            data-msg-none="{{ __('Tidak ada kursus yang cocok dengan pencarian.') }}">
            @include('peserta.partials.course-search', [
                'total' => $courses->count(),
                'summary' => __(':count kursus tersedia', ['count' => $courses->count()]),
            ])
            <div class="peserta-kursus-toolbar">
                <div class="peserta-kursus-filters" role="group" aria-label="{{ __('Filter kategori') }}">
                    <button type="button" class="filter-btn active" data-filter-btn data-filter="all">{{ __('Semua') }}</button>
                    @foreach ($kategoris as $kategori)
                        <button type="button" class="filter-btn" data-filter-btn
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

        <div class="peserta-kursus-empty d-none" id="pesertaKatalogNoResults">
            <i class="bi bi-search fs-1 d-block mb-2 opacity-50"></i>
            <p class="mb-0">{{ __('Tidak ada kursus yang cocok dengan pencarian.') }}</p>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('backend') }}/assets/js/peserta-course-grid-filter.js"></script>
@endpush
