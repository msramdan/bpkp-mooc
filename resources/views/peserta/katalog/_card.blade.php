@php
    $isEnrolled = $enrolledIds->contains($course->id);
@endphp

<article class="peserta-kursus-card peserta-katalog-item" data-kategori="{{ $course->kategori }}"
    data-search="{{ strtolower($course->judul.' '.$course->kode.' '.$course->kategori.' '.$course->instruktur.' '.$course->level) }}">
    <div class="peserta-kursus-card__thumb">
        <img src="{{ $course->thumbnail }}" alt="{{ $course->judul }}" loading="lazy"
            onerror="this.src='https://placehold.co/640x360/2b478b/ffffff?text={{ urlencode($course->kode) }}'">
        @if ($isEnrolled)
            <span class="peserta-kursus-card__status badge bg-success-transparent">{{ __('Sudah terdaftar') }}</span>
        @else
            <span class="peserta-kursus-card__status badge bg-primary-transparent">{{ $course->level }}</span>
        @endif
        <span class="peserta-kursus-card__duration">
            <i class="bi bi-star-fill text-warning"></i> {{ number_format($course->rating, 1) }}
            · {{ $course->durasi_jam }} {{ __('jam') }}
        </span>
        <div class="peserta-kursus-card__play" aria-hidden="true">
            <span class="peserta-kursus-card__play-icon"><i class="bi bi-play-fill"></i></span>
        </div>
    </div>
    <div class="peserta-kursus-card__body">
        <h3 class="peserta-kursus-card__title">{{ $course->judul }}</h3>
        <p class="peserta-kursus-card__meta mb-0">
            <strong>{{ $course->instruktur }}</strong><br>
            {{ $course->kategori }} · {{ $course->modul_total }} {{ __('modul') }}
        </p>
        <div class="peserta-kursus-card__footer d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
            <span class="badge bg-light text-dark">{{ $course->kode }}</span>
            <span class="text-muted fs-12">
                <i class="bi bi-people me-1"></i>{{ $course->enrollments_count }} {{ __('peserta') }}
            </span>
        </div>
        <button type="button" class="btn btn-sm btn-primary btn-wave w-100 mt-2" disabled>
            {{ $isEnrolled ? __('Buka kursus') : __('Daftar kursus') }}
        </button>
    </div>
</article>
