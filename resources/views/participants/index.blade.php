@extends('layouts.app')

@section('title', __('Peserta'))

@section('content')
    <div class="admin-participants-page">
        <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="page-title fw-medium fs-18 mb-1">{{ __('Peserta') }}</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Peserta') }}</li>
                </ol>
                <p class="admin-participants-page__lead text-muted mb-0 mt-1">
                    {{ __('Daftar akun peserta dan jumlah kursus yang diikuti.') }}
                </p>
            </div>
            @can('course view')
                <a href="{{ route('courses.index') }}" class="btn btn-outline-primary btn-wave">
                    <i class="bi bi-mortarboard me-1"></i>{{ __('Kelola kursus') }}
                </a>
            @endcan
        </div>

        <x-alert />

        <div class="admin-participants-panel">
            <form method="GET" action="{{ route('participants.index') }}" class="admin-participants-filters admin-participants-filters--compact">
                @if ($filters['letter'] !== '')
                    <input type="hidden" name="letter" value="{{ $filters['letter'] }}">
                @endif
                <div class="admin-participants-filters__search">
                    <label class="admin-participants-filters__label" for="participant-search">{{ __('Cari') }}</label>
                    <div class="admin-participants-filters__search-field">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" name="q" id="participant-search" value="{{ $filters['q'] }}"
                            class="form-control form-control-sm" placeholder="{{ __('Nama atau email peserta...') }}">
                    </div>
                </div>
                <div class="admin-participants-filters__item">
                    <label class="admin-participants-filters__label" for="participant-course">{{ __('Kursus') }}</label>
                    <select name="course_id" id="participant-course" class="form-select form-select-sm" data-trigger
                        data-placeholder="{{ __('Semua kursus') }}"
                        data-search-placeholder="{{ __('Cari kursus') }}">
                        <option value="">{{ __('Semua kursus') }}</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected($filters['course_id'] === (string) $course->id)>
                                {{ $course->judul }} ({{ $course->kode }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-participants-filters__actions">
                    <button type="submit" class="btn btn-sm btn-primary btn-wave">
                        <i class="bi bi-funnel me-1"></i>{{ __('Terapkan') }}
                    </button>
                    <a href="{{ route('participants.index') }}" class="btn btn-sm btn-light btn-wave">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset') }}
                    </a>
                </div>
            </form>

            @php
                $letterQuery = array_filter([
                    'q' => $filters['q'] !== '' ? $filters['q'] : null,
                    'course_id' => $filters['course_id'] !== '' ? $filters['course_id'] : null,
                ], fn ($v) => $v !== null);
            @endphp
            <div class="admin-participants-alpha" role="navigation" aria-label="{{ __('Filter nama depan') }}">
                <div class="admin-participants-alpha__meta">
                    <span class="admin-participants-alpha__count">
                        {{ __(':count peserta ditemukan', ['count' => number_format($participants->total())]) }}
                    </span>
                </div>
                <div class="admin-participants-alpha__row">
                    <span class="admin-participants-alpha__label">{{ __('Nama Depan') }}</span>
                    <div class="admin-participants-alpha__letters">
                        <a href="{{ route('participants.index', $letterQuery) }}"
                            class="admin-participants-alpha__chip {{ $filters['letter'] === '' ? 'is-active' : '' }}">
                            {{ __('Semua') }}
                        </a>
                        <span class="admin-participants-alpha__group">
                            @foreach (array_slice($alphabet, 0, 13) as $char)
                                <a href="{{ route('participants.index', array_merge($letterQuery, ['letter' => $char])) }}"
                                    class="admin-participants-alpha__chip admin-participants-alpha__chip--letter {{ $filters['letter'] === $char ? 'is-active' : '' }}">{{ $char }}</a>
                            @endforeach
                        </span>
                        <span class="admin-participants-alpha__group">
                            @foreach (array_slice($alphabet, 13) as $char)
                                <a href="{{ route('participants.index', array_merge($letterQuery, ['letter' => $char])) }}"
                                    class="admin-participants-alpha__chip admin-participants-alpha__chip--letter {{ $filters['letter'] === $char ? 'is-active' : '' }}">{{ $char }}</a>
                            @endforeach
                        </span>
                    </div>
                </div>
            </div>

            <div class="admin-participants-table-wrap">
                <div class="table-responsive">
                    <table class="table admin-participants-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th scope="col" class="admin-participants-table__num">#</th>
                                <th scope="col">{{ __('Peserta') }}</th>
                                <th scope="col" class="text-center">{{ __('Kursus diikuti') }}</th>
                                <th scope="col">{{ __('Surel') }}</th>
                                <th scope="col" class="text-center admin-participants-table__actions">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($participants as $participant)
                                @php
                                    $courseCount = (int) $participant->course_enrollments_count;
                                @endphp
                                <tr>
                                    <td class="admin-participants-table__num text-muted">
                                        {{ $participants->firstItem() + $loop->index }}
                                    </td>
                                    <td>
                                        <div class="admin-participants-person">
                                            <span class="admin-participants-person__avatar" aria-hidden="true">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($participant->name ?: '?', 0, 1)) }}
                                            </span>
                                            <div class="min-w-0">
                                                <div class="admin-participants-person__name text-truncate">{{ $participant->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if ($courseCount > 0)
                                            <button type="button"
                                                class="admin-participants-course-count js-participant-detail"
                                                data-bs-toggle="modal"
                                                data-bs-target="#participantCoursesModal"
                                                data-name="{{ $participant->name }}"
                                                data-email="{{ $participant->email }}"
                                                data-url="{{ route('participants.courses', $participant) }}"
                                                title="{{ __('Lihat detail') }}">
                                                <span class="admin-participants-course-count__num">{{ number_format($courseCount) }}</span>
                                                <span class="admin-participants-course-count__label">{{ __('kursus') }}</span>
                                            </button>
                                        @else
                                            <span class="text-muted fs-13">0 {{ __('kursus') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $participant->email }}</td>
                                    <td class="text-center admin-participants-table__actions">
                                        <button type="button"
                                            class="btn btn-sm btn-icon btn-primary-light btn-wave js-participant-detail"
                                            data-bs-toggle="modal"
                                            data-bs-target="#participantCoursesModal"
                                            data-name="{{ $participant->name }}"
                                            data-email="{{ $participant->email }}"
                                            data-url="{{ route('participants.courses', $participant) }}"
                                            title="{{ __('Lihat detail') }}"
                                            aria-label="{{ __('Lihat detail') }}">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="admin-participants-empty">
                                            <i class="bi bi-people"></i>
                                            <p class="mb-1 fw-medium">{{ __('Belum ada data peserta') }}</p>
                                            <p class="mb-0 text-muted fs-13">
                                                {{ __('Daftarkan peserta dari halaman detail masing-masing kursus.') }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($participants->hasPages() || $participants->total() > 0)
                <div class="admin-participants-footer">
                    <span class="text-muted fs-12">
                        {{ __('Menampilkan :from–:to dari :total peserta', [
                            'from' => $participants->firstItem() ?? 0,
                            'to' => $participants->lastItem() ?? 0,
                            'total' => $participants->total(),
                        ]) }}
                    </span>
                    <div class="admin-participants-footer__pager">
                        {{ $participants->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="participantCoursesModal" tabindex="-1" aria-labelledby="participantCoursesModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="min-w-0">
                        <h5 class="modal-title mb-0" id="participantCoursesModalTitle">{{ __('Detail peserta') }}</h5>
                        <p class="text-muted fs-13 mb-0 mt-1 text-truncate" id="participantCoursesModalSubtitle"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="admin-participants-modal-profile" id="participantCoursesProfile">
                        <span class="admin-participants-modal-profile__avatar" id="participantCoursesAvatar" aria-hidden="true">?</span>
                        <div class="min-w-0">
                            <div class="admin-participants-modal-profile__name" id="participantCoursesName"></div>
                            <div class="admin-participants-modal-profile__email" id="participantCoursesEmail"></div>
                        </div>
                    </div>
                    <div class="admin-participants-modal-section">
                        <h6 class="admin-participants-modal-section__title">{{ __('Kursus yang diikuti') }}</h6>
                    </div>
                    <div class="admin-participants-modal-loading text-center text-muted py-5" id="participantCoursesLoading">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        {{ __('Loading…') }}
                    </div>
                    <div class="admin-participants-modal-empty d-none" id="participantCoursesEmpty">
                        <i class="bi bi-journal-x"></i>
                        <p class="mb-0">{{ __('Belum mengikuti kursus.') }}</p>
                    </div>
                    <ul class="list-group list-group-flush admin-participants-modal-list" id="participantCoursesList"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('backend/assets/css/admin-participants.css') }}?v={{ @filemtime(public_path('backend/assets/css/admin-participants.css')) ?: time() }}">
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('participantCoursesModal');
            var listEl = document.getElementById('participantCoursesList');
            var emptyEl = document.getElementById('participantCoursesEmpty');
            var loadingEl = document.getElementById('participantCoursesLoading');
            var subtitleEl = document.getElementById('participantCoursesModalSubtitle');
            var nameEl = document.getElementById('participantCoursesName');
            var emailEl = document.getElementById('participantCoursesEmail');
            var avatarEl = document.getElementById('participantCoursesAvatar');
            if (!modalEl || !listEl) return;

            var labels = {
                open: @json(__('Buka di kursus')),
                progress: @json(__('Progres')),
                failed: @json(__('Could not load data.')),
                empty: @json(__('Belum mengikuti kursus.')),
            };
            var cache = Object.create(null);
            var activeUrl = '';

            function escapeHtml(value) {
                return String(value == null ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function setProfile(name, email) {
                var safeName = name || '—';
                var initial = (safeName.trim().charAt(0) || '?').toUpperCase();
                nameEl.textContent = safeName;
                emailEl.textContent = email || '—';
                avatarEl.textContent = initial;
                subtitleEl.textContent = email || safeName;
            }

            function setState(state) {
                loadingEl.classList.toggle('d-none', state !== 'loading');
                emptyEl.classList.toggle('d-none', state !== 'empty');
                listEl.classList.toggle('d-none', state !== 'list');
            }

            function renderCourses(courses) {
                listEl.innerHTML = '';
                if (!courses.length) {
                    emptyEl.querySelector('p').textContent = labels.empty;
                    setState('empty');
                    return;
                }

                setState('list');
                courses.forEach(function (course) {
                    var meta = [course.code, course.category].filter(Boolean).join(' · ');
                    var li = document.createElement('li');
                    li.className = 'list-group-item admin-participants-modal-item';
                    li.innerHTML =
                        '<div class="admin-participants-modal-item__body min-w-0">' +
                            '<div class="admin-participants-modal-item__title">' + escapeHtml(course.title) + '</div>' +
                            (meta ? '<div class="admin-participants-modal-item__meta">' + escapeHtml(meta) + '</div>' : '') +
                            '<div class="admin-participants-modal-item__stats">' +
                                '<span class="badge bg-primary-transparent">' + escapeHtml(course.status || '—') + '</span>' +
                                '<span class="text-muted fs-12">' + labels.progress + ': ' + Number(course.progress || 0) + '%</span>' +
                            '</div>' +
                        '</div>' +
                        '<a href="' + escapeHtml(course.url) + '" class="btn btn-sm btn-outline-primary btn-wave">' +
                            '<i class="bi bi-box-arrow-up-right me-1"></i>' + labels.open +
                        '</a>';
                    listEl.appendChild(li);
                });
            }

            document.querySelectorAll('.js-participant-detail').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var name = btn.getAttribute('data-name') || '';
                    var email = btn.getAttribute('data-email') || '';
                    var url = btn.getAttribute('data-url') || '';
                    activeUrl = url;
                    setProfile(name, email);
                    listEl.innerHTML = '';

                    if (!url) {
                        emptyEl.querySelector('p').textContent = labels.empty;
                        setState('empty');
                        return;
                    }

                    if (cache[url]) {
                        renderCourses(cache[url]);
                        return;
                    }

                    setState('loading');
                    fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    })
                        .then(function (res) {
                            if (!res.ok) throw new Error('load failed');
                            return res.json();
                        })
                        .then(function (payload) {
                            if (url !== activeUrl) return;
                            var courses = Array.isArray(payload.courses) ? payload.courses : [];
                            cache[url] = courses;
                            if (payload.name || payload.email) {
                                setProfile(payload.name || name, payload.email || email);
                            }
                            renderCourses(courses);
                        })
                        .catch(function () {
                            if (url !== activeUrl) return;
                            setState('empty');
                            emptyEl.querySelector('p').textContent = labels.failed;
                        });
                });
            });
        });
    </script>
@endpush
