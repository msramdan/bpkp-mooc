<article class="admin-course-card">
    <a href="{{ route('courses.show', $course) }}" class="admin-course-card__media-link" tabindex="-1" aria-hidden="true">
        <div class="admin-course-card__media">
            <x-course-thumbnail :course="$course" class="admin-course-card__image" />
            <div class="admin-course-card__media-shade" aria-hidden="true"></div>
            @if ($course->is_published)
                <span class="admin-course-card__status admin-course-card__status--published">{{ __('Dipublikasikan') }}</span>
            @else
                <span class="admin-course-card__status admin-course-card__status--draft">{{ __('Draft') }}</span>
            @endif
            @if ($course->level)
                <span class="admin-course-card__level">{{ __($course->level) }}</span>
            @endif
        </div>
    </a>

    <div class="admin-course-card__body">
        @if ($course->kategori)
            <span class="admin-course-card__category">{{ $course->kategori }}</span>
        @endif

        <h3 class="admin-course-card__title">
            <a href="{{ route('courses.show', $course) }}">{{ $course->judul }}</a>
        </h3>

        @if ($course->relationLoaded('tags') && $course->tags->isNotEmpty())
            <div class="admin-course-card__tags">
                @foreach ($course->tags->take(3) as $tag)
                    <span class="admin-course-card__tag">{{ $tag->name }}</span>
                @endforeach
                @if ($course->tags->count() > 3)
                    <span class="admin-course-card__tag">+{{ $course->tags->count() - 3 }}</span>
                @endif
            </div>
        @endif

        <div class="admin-course-card__stats">
            <span class="admin-course-card__stat">
                <i class="bi bi-collection-play"></i>
                {{ (int) ($course->modules_count ?? $course->topicsCount()) }} {{ __('topik') }}
            </span>
            <span class="admin-course-card__stat">
                <i class="bi bi-people"></i>
                {{ number_format((int) ($course->enrollments_count ?? 0)) }} {{ __('peserta') }}
            </span>
        </div>

        <div class="admin-course-card__footer">
            <span class="admin-course-card__code">{{ $course->kode }}</span>
            <div class="admin-course-card__actions">
                @include('courses.include.card-actions', ['model' => $course])
            </div>
        </div>
    </div>
</article>
