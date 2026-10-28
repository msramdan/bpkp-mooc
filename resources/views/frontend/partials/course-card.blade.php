@php
    $delay = 100 + (($loop->index ?? 0) * 80);
    $levelLabel = __($course->level ?: 'Pemula');
    $tags = $course->relationLoaded('tags') ? $course->tags : collect();
@endphp
<div class="col-xl-3 col-lg-4 col-sm-6 d-flex wow fadeInUp" data-wow-delay="{{ $delay }}ms">
    <article class="landing-course-card w-100">
        <a href="#pelatihan" class="landing-course-card__media">
            <x-course-thumbnail :course="$course" class="landing-course-card__img" />
            <span class="landing-course-card__badge">
                <i class="icon-book"></i> {{ $course->modul_total }} {{ __('Modul') }}
            </span>
        </a>
        <div class="landing-course-card__body">
            <div class="landing-course-card__meta">
                <span class="landing-course-card__category">{{ $course->kategori ?: __('Umum') }}</span>
                <span class="landing-course-card__level">{{ $levelLabel }}</span>
            </div>
            <h3 class="landing-course-card__title">
                <a href="#pelatihan">{{ $course->judul }}</a>
            </h3>
            @if ($tags->isNotEmpty())
                <div class="landing-course-card__tags">
                    @foreach ($tags->take(2) as $tag)
                        <span class="landing-course-card__tag" title="{{ $tag->name }}">{{ $tag->name }}</span>
                    @endforeach
                    @if ($tags->count() > 2)
                        <span class="landing-course-card__tag">+{{ $tags->count() - 2 }}</span>
                    @endif
                </div>
            @endif
            <div class="landing-course-card__footer">
                <span class="landing-course-card__author" title="{{ $course->instruktur }}">{{ $course->instruktur }}</span>
                <span class="landing-course-card__participants">
                    <i class="icon-account-1"></i> {{ number_format($course->enrollments_count) }}
                </span>
            </div>
        </div>
    </article>
</div>
