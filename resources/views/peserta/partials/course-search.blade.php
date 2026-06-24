@props([
    'placeholder' => __('Cari judul, kode, kategori, atau instruktur...'),
    'total' => 0,
    'summary' => '',
])

<div class="peserta-course-panel__search">
    <label class="peserta-course-search" for="pesertaCourseSearch">
        <i class="bi bi-search peserta-course-search__icon" aria-hidden="true"></i>
        <input type="search" id="pesertaCourseSearch" class="peserta-course-search__input" data-search-input
            placeholder="{{ $placeholder }}" autocomplete="off"
            aria-label="{{ __('Cari kursus') }}">
        <button type="button" class="peserta-course-search__clear d-none" data-search-clear
            aria-label="{{ __('Hapus pencarian') }}">
            <i class="bi bi-x-lg"></i>
        </button>
    </label>
</div>
<p class="peserta-course-panel__summary" data-result-summary data-label-template="{{ $summary }}">
    {{ $summary }}
</p>
