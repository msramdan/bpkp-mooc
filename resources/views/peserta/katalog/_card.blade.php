@php
    $isEnrolled = $enrolledIds->contains($course->id);
@endphp

<article class="peserta-kursus-card peserta-kursus-card--elevated peserta-katalog-item{{ $isEnrolled ? ' peserta-kursus-card--enrolled' : '' }}"
    data-kategori="{{ $course->kategori }}"
    data-search="{{ strtolower($course->judul.' '.$course->kode.' '.$course->kategori.' '.$course->instruktur.' '.$course->level) }}">
    <div class="peserta-kursus-card__media">
        <x-course-thumbnail :course="$course" class="peserta-kursus-card__image" />
        <div class="peserta-kursus-card__media-shade" aria-hidden="true"></div>
        @if ($isEnrolled)
            <span class="peserta-kursus-card__status peserta-kursus-card__status--success">{{ __('Sudah terdaftar') }}</span>
        @else
            <span class="peserta-kursus-card__status peserta-kursus-card__status--primary">{{ $course->level }}</span>
        @endif
        <span class="peserta-kursus-card__duration">
            {{ number_format($course->rating, 1) }}
            <span class="peserta-kursus-card__duration-dot">·</span>
            {{ $course->durasi_jam }} {{ __('jam') }}
        </span>
    </div>
    <div class="peserta-kursus-card__body">
        <span class="peserta-kursus-card__category">{{ $course->kategori }}</span>
        <h3 class="peserta-kursus-card__title">{{ $course->judul }}</h3>
        <p class="peserta-kursus-card__instructor">
            <i class="bi bi-person-circle"></i>
            <span>{{ $course->instruktur }}</span>
        </p>
        <div class="peserta-kursus-card__footer">
            <span class="peserta-kursus-card__chip">{{ $course->kode }}</span>
            <span class="peserta-kursus-card__chip peserta-kursus-card__chip--muted">
                {{ $course->modul_total }} {{ __('modul') }}
            </span>
            <span class="peserta-kursus-card__chip peserta-kursus-card__chip--muted">
                {{ $course->enrollments_count }} {{ __('peserta') }}
            </span>
        </div>
        <div class="peserta-kursus-card__actions">
            @if ($isEnrolled)
                <a href="{{ route('peserta.kursus.show', $course) }}" class="btn btn-success btn-wave peserta-kursus-card__cta w-100">
                    {{ __('Buka kursus') }}
                </a>
            @else
                <form action="{{ route('peserta.katalog.enroll', $course) }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-wave peserta-kursus-card__cta w-100">
                        {{ __('Daftar kursus') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</article>
