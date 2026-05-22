@props([
    'placeholder' => __('Cari judul, kode, kategori, atau instruktur...'),
    'total' => 0,
    'summary' => '',
])

<div class="peserta-kursus-search">
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-search text-muted"></i>
        </span>
        <input type="search" class="form-control border-start-0 ps-0" data-search-input
            placeholder="{{ $placeholder }}" autocomplete="off"
            aria-label="{{ __('Cari kursus') }}">
        <button type="button" class="btn btn-light border d-none" data-search-clear
            aria-label="{{ __('Hapus pencarian') }}">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</div>
<p class="text-muted mb-0 fs-13 mt-2" data-result-summary data-label-template="{{ $summary }}">
    {{ $summary }}
</p>
