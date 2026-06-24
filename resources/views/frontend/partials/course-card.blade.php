@php
    $delay = 100 + (($loop->index ?? 0) * 100);
    $levelLabel = match (strtolower((string) $course->level)) {
        'pemula' => 'Pemula',
        'menengah' => 'Menengah',
        'lanjutan' => 'Lanjutan',
        default => ucfirst(strtolower((string) $course->level)),
    };
@endphp
<div class="col-xl-4 col-lg-6 d-flex wow fadeInUp" data-wow-delay="{{ $delay }}ms">
    <div class="course-one__item landing-course-card w-100">
        <div class="course-one__thumb">
            <x-course-thumbnail :course="$course" />
        </div>
        <div class="course-one__content landing-course-card__body">
            <div class="landing-course-card__main">
                <div class="course-one__time">
                    {{ $course->modul_total }} Modul
                    @if ($course->durasi_jam > 0)
                        · {{ $course->durasi_jam }} Jam
                    @endif
                </div>
                <div class="course-one__ratings">
                    <span class="badge bg-primary-transparent">{{ $course->kategori }}</span>
                </div>
                <h3 class="course-one__title landing-course-card__title">
                    <a href="#pelatihan">{{ $course->judul }}</a>
                </h3>
            </div>
            <div class="course-one__bottom landing-course-card__footer">
                <div class="course-one__author">
                    <span class="course-one__author__name d-block">{{ $course->instruktur }}</span>
                    <p class="course-one__author__designation mb-0">{{ $levelLabel }}</p>
                </div>
                <div class="course-one__meta">
                    <p class="course-one__meta__class mb-0">{{ number_format($course->enrollments_count) }} Peserta</p>
                </div>
            </div>
        </div>
    </div>
</div>