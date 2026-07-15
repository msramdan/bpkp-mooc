<button type="button"
    class="admin-participants-course-count js-user-courses"
    data-bs-toggle="modal"
    data-bs-target="#userCoursesModal"
    data-name="{{ $user->name }}"
    data-email="{{ $user->email }}"
    data-url="{{ route('users.courses', $user) }}"
    title="{{ __('Lihat detail') }}">
    <span class="admin-participants-course-count__num">{{ number_format($count) }}</span>
    <span class="admin-participants-course-count__label">{{ __('kursus') }}</span>
</button>
