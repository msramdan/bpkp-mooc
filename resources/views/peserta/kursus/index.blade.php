@extends('layouts.app')

@section('title', __('Kursus Saya'))

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-kursus-grid.css" rel="stylesheet">
@endpush

@section('content')
    @include('peserta.partials.page-header', ['title' => __('Kursus Saya')])

    @if ($enrollments->isEmpty())
        <div class="peserta-kursus-empty">
            <i class="bi bi-collection-play fs-1 d-block mb-2 opacity-50"></i>
            <p class="mb-0">{{ __('Belum ada kursus terdaftar.') }}</p>
        </div>
    @else
        <div class="peserta-grid-filter-wrap" data-peserta-grid-filter data-grid="#pesertaKursusGrid"
            data-item-selector=".peserta-kursus-item" data-filter-key="status"
            data-total="{{ $enrollments->count() }}"
            data-no-results="#pesertaKursusNoResults"
            data-msg-visible="{{ __('Menampilkan :visible dari :total kursus') }}"
            data-msg-none="{{ __('Tidak ada kursus yang cocok dengan pencarian.') }}">
            @include('peserta.partials.course-search', [
                'total' => $enrollments->count(),
                'summary' => __(':count kursus terdaftar', ['count' => $enrollments->count()]),
            ])
            <div class="peserta-kursus-toolbar">
                <div class="peserta-kursus-filters" role="group" aria-label="{{ __('Filter kursus') }}">
                    <button type="button" class="filter-btn active" data-filter-btn data-filter="all">{{ __('Semua') }}</button>
                    <button type="button" class="filter-btn" data-filter-btn data-filter="Berlangsung">{{ __('Berlangsung') }}</button>
                    <button type="button" class="filter-btn" data-filter-btn data-filter="Selesai">{{ __('Selesai') }}</button>
                </div>
            </div>
        </div>

        <div class="peserta-kursus-grid" id="pesertaKursusGrid">
            @foreach ($enrollments as $enrollment)
                @include('peserta.kursus._card', ['enrollment' => $enrollment])
            @endforeach
        </div>

        <div class="peserta-kursus-empty d-none" id="pesertaKursusNoResults">
            <i class="bi bi-search fs-1 d-block mb-2 opacity-50"></i>
            <p class="mb-0">{{ __('Tidak ada kursus yang cocok dengan pencarian.') }}</p>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('backend') }}/assets/js/peserta-course-grid-filter.js"></script>
@endpush
