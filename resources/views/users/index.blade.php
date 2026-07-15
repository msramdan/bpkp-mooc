@extends('layouts.app')

@section('title', __('Users'))

@section('content')
    <div class="admin-participants-page">
        <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Users') }}</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Users') }}</li>
                </ol>
            </div>
            @can('user create')
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-wave">
                    <i class="ri-add-line align-middle me-1"></i>{{ __('Tambah') }}
                </a>
            @endcan
        </div>

        <x-alert />

        <div class="admin-participants-panel admin-participants-panel--filters">
            <div class="admin-participants-filters admin-participants-filters--compact">
                <div class="admin-participants-filters__search">
                    <label class="admin-participants-filters__label" for="user-search">{{ __('Cari') }}</label>
                    <div class="admin-participants-filters__search-field">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" id="user-search" class="form-control form-control-sm"
                            placeholder="{{ __('Nama atau email peserta...') }}">
                    </div>
                </div>
                <div class="admin-participants-filters__item">
                    <label class="admin-participants-filters__label" for="user-course">{{ __('Kursus') }}</label>
                    <select id="user-course" class="form-select form-select-sm" data-trigger
                        data-placeholder="{{ __('Semua kursus') }}"
                        data-search-placeholder="{{ __('Cari kursus') }}">
                        <option value="">{{ __('Semua kursus') }}</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->judul }} ({{ $course->kode }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-participants-filters__actions">
                    <button type="button" id="user-filter-apply" class="btn btn-sm btn-primary btn-wave">
                        <i class="bi bi-funnel me-1"></i>{{ __('Terapkan') }}
                    </button>
                    <button type="button" id="user-filter-reset" class="btn btn-sm btn-light btn-wave">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset') }}
                    </button>
                </div>
            </div>

            <div class="admin-participants-alpha" role="navigation" aria-label="{{ __('Filter nama depan') }}">
                <div class="admin-participants-alpha__letters">
                    <button type="button" class="admin-participants-alpha__chip is-active js-user-letter" data-letter="">
                        {{ __('Semua') }}
                    </button>
                    <span class="admin-participants-alpha__group">
                        @foreach (array_slice($alphabet, 0, 13) as $char)
                            <button type="button"
                                class="admin-participants-alpha__chip admin-participants-alpha__chip--letter js-user-letter"
                                data-letter="{{ $char }}">{{ $char }}</button>
                        @endforeach
                    </span>
                    <span class="admin-participants-alpha__group">
                        @foreach (array_slice($alphabet, 13) as $char)
                            <button type="button"
                                class="admin-participants-alpha__chip admin-participants-alpha__chip--letter js-user-letter"
                                data-letter="{{ $char }}">{{ $char }}</button>
                        @endforeach
                    </span>
                </div>
            </div>
        </div>

        <div class="admin-participants-panel admin-participants-panel--list">
            <div class="card-body px-3 pt-3 pb-2">
                <div class="table-responsive">
                    <table id="users-table" class="table table-striped table-bordered text-nowrap w-100 align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Avatar') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th class="text-center">{{ __('Kursus diikuti') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th class="text-center">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('users.partials.detail-modal')
    @include('users.partials.courses-modal')
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/admin-participants.css') }}?v={{ @filemtime(public_path('backend/assets/css/admin-participants.css')) ?: time() }}">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    @include('partials.js.datatable-modal-detail-utils')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var M = window.BpkpDataTableModal;
            if (!M) {
                return;
            }

            var filterState = { course_id: '', letter: '' };
            var searchInput = document.getElementById('user-search');
            var courseSelect = document.getElementById('user-course');

            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('users.index')),
                    data: function(d) {
                        d.course_id = filterState.course_id;
                        d.letter = filterState.letter;
                    }
                },
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                order: [
                    [1, 'asc']
                ],
                language: (document.documentElement.getAttribute('lang') || '').indexOf('en') === 0
                    ? {}
                    : { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                columns: [{
                        data: 'avatar',
                        name: 'avatar',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            var src = data || '';
                            return '<span class="avatar avatar-sm"><img src="' + $('<div>').text(src).html() +
                                '" class="rounded-circle" width="36" height="36" alt=""></span>';
                        }
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'courses_count',
                        name: 'course_enrollments_count',
                        orderable: true,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'role',
                        name: 'role',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                dom: "<'row align-items-center mb-3'<'col-md-6'l><'col-md-6'>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3'<'col-md-5'i><'col-md-7'p>>"
            });

            function reloadUsers() {
                var q = searchInput ? searchInput.value.trim() : '';
                filterState.course_id = courseSelect ? (courseSelect.value || '') : '';
                table.search(q).draw();
            }

            document.getElementById('user-filter-apply')?.addEventListener('click', reloadUsers);
            document.getElementById('user-filter-reset')?.addEventListener('click', function() {
                if (searchInput) searchInput.value = '';
                if (courseSelect) {
                    courseSelect.value = '';
                    if (courseSelect._choices) {
                        courseSelect._choices.setChoiceByValue('');
                    } else if (typeof Choices !== 'undefined' && courseSelect.choices) {
                        courseSelect.choices.setChoiceByValue('');
                    }
                    courseSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
                filterState.course_id = '';
                filterState.letter = '';
                document.querySelectorAll('.js-user-letter').forEach(function(el) {
                    el.classList.toggle('is-active', el.getAttribute('data-letter') === '');
                });
                table.search('').draw();
            });

            searchInput?.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    reloadUsers();
                }
            });

            document.querySelectorAll('.js-user-letter').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    filterState.letter = btn.getAttribute('data-letter') || '';
                    document.querySelectorAll('.js-user-letter').forEach(function(el) {
                        el.classList.toggle('is-active', el === btn);
                    });
                    reloadUsers();
                });
            });

            // Detail profile modal
            var modalEl = document.getElementById('userDetailModal');
            if (modalEl) {
                var ids = {
                    loading: 'userDetailModalLoading',
                    error: 'userDetailModalError',
                    content: 'userDetailModalContent',
                };

                function byId(id) {
                    return document.getElementById(id);
                }

                function resetModalBody() {
                    var loading = byId(ids.loading);
                    var err = byId(ids.error);
                    var content = byId(ids.content);
                    if (loading) loading.classList.remove('d-none');
                    if (err) {
                        err.classList.add('d-none');
                        err.textContent = '';
                    }
                    if (content) {
                        content.classList.add('d-none');
                        content.innerHTML = '';
                    }
                }

                function showLoadError(message) {
                    var loading = byId(ids.loading);
                    var err = byId(ids.error);
                    if (loading) loading.classList.add('d-none');
                    if (err) {
                        err.textContent = message;
                        err.classList.remove('d-none');
                    }
                }

                var userModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                var msgLoadFail = @json(__('Could not load data.'));
                var msgNotFound = @json(__('Not found'));
                var lblName = @json(__('Name'));
                var lblEmail = @json(__('Email'));
                var lblRole = @json(__('Role'));
                var lblVerified = @json(__('Email verified at'));
                var lblCreated = @json(__('Created at'));
                var lblUpdated = @json(__('Updated at'));

                modalEl.addEventListener('hidden.bs.modal', resetModalBody);

                var fetchController = null;
                $(document).on('click', '.js-open-user-detail', function(e) {
                    e.preventDefault();
                    var url = $(this).data('url');
                    if (!url) return;
                    if (fetchController) fetchController.abort();
                    fetchController = new AbortController();
                    resetModalBody();
                    userModal.show();

                    M.jsonDetailFetch(url, fetchController.signal)
                        .then(function(r) {
                            if (r.status === 404) throw new Error(msgNotFound);
                            if (!r.ok) throw new Error(msgLoadFail);
                            return r.json();
                        })
                        .then(function(d) {
                            if (fetchController.signal.aborted) return;
                            var loadingEl = byId(ids.loading);
                            if (loadingEl) loadingEl.classList.add('d-none');
                            var role = d.role != null && d.role !== '' ? M.escHtml(d.role) : '—';
                            var verified = d.email_verified_at != null && d.email_verified_at !== '' ?
                                M.escHtml(d.email_verified_at) : '—';
                            var html = '<div class="row g-3">' +
                                '<div class="col-auto"><span class="avatar avatar-xl"><img src="' + M.escAttr(d.avatar) +
                                '" class="rounded-circle" width="80" height="80" alt=""></span></div>' +
                                '<div class="col"><table class="table table-sm table-borderless mb-0">' +
                                '<tr><th class="text-muted fw-normal" style="width:11rem">' + M.escHtml(lblName) +
                                '</th><td>' + M.escHtml(d.name) + '</td></tr>' +
                                '<tr><th class="text-muted fw-normal">' + M.escHtml(lblEmail) + '</th><td>' + M.escHtml(d.email) + '</td></tr>' +
                                '<tr><th class="text-muted fw-normal">' + M.escHtml(lblRole) + '</th><td>' + role + '</td></tr>' +
                                '<tr><th class="text-muted fw-normal">' + M.escHtml(lblVerified) + '</th><td>' + verified + '</td></tr>' +
                                '<tr><th class="text-muted fw-normal">' + M.escHtml(lblCreated) + '</th><td>' + M.escHtml(d.created_at) + '</td></tr>' +
                                '<tr><th class="text-muted fw-normal">' + M.escHtml(lblUpdated) + '</th><td>' + M.escHtml(d.updated_at) + '</td></tr>' +
                                '</table></div></div>';
                            var wrap = byId(ids.content);
                            if (wrap) {
                                wrap.innerHTML = html;
                                wrap.classList.remove('d-none');
                            }
                        })
                        .catch(function(err) {
                            if (err.name === 'AbortError') return;
                            showLoadError(err && err.message ? err.message : msgLoadFail);
                        });
                });
            }

            // Courses modal
            var coursesModalEl = document.getElementById('userCoursesModal');
            var listEl = document.getElementById('userCoursesList');
            var emptyEl = document.getElementById('userCoursesEmpty');
            var loadingEl = document.getElementById('userCoursesLoading');
            var nameEl = document.getElementById('userCoursesName');
            var emailEl = document.getElementById('userCoursesEmail');
            var avatarEl = document.getElementById('userCoursesAvatar');
            var subtitleEl = document.getElementById('userCoursesModalSubtitle');

            if (coursesModalEl && listEl) {
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
                    nameEl.textContent = safeName;
                    emailEl.textContent = email || '—';
                    avatarEl.textContent = (safeName.trim().charAt(0) || '?').toUpperCase();
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
                    courses.forEach(function(course) {
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

                $(document).on('click', '.js-user-courses', function() {
                    var btn = this;
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
                        .then(function(res) {
                            if (!res.ok) throw new Error('load failed');
                            return res.json();
                        })
                        .then(function(payload) {
                            if (url !== activeUrl) return;
                            var courses = Array.isArray(payload.courses) ? payload.courses : [];
                            cache[url] = courses;
                            if (payload.name || payload.email) {
                                setProfile(payload.name || name, payload.email || email);
                            }
                            renderCourses(courses);
                        })
                        .catch(function() {
                            if (url !== activeUrl) return;
                            setState('empty');
                            emptyEl.querySelector('p').textContent = labels.failed;
                        });
                });
            }
        });
    </script>
@endpush
