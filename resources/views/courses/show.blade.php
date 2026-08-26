@extends('layouts.app')

@section('title', $course->judul)

@section('content')
    <div class="admin-course-detail">
        <div class="my-4 page-header-breadcrumb d-flex align-items-start justify-content-between flex-wrap gap-2">
            <div class="min-w-0">
                <h1 class="page-title fw-medium fs-18 mb-1 text-truncate">{{ $course->judul }}</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">{{ __('Kursus') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $course->kode }}</li>
                </ol>
            </div>
        </div>

        <x-alert />

        <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ ($activeTab ?? 'info') === 'info' ? 'active' : '' }}"
                href="{{ route('courses.show', [$course, 'tab' => 'info']) }}">
                {{ __('Informasi kursus') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($activeTab ?? '') === 'modules' ? 'active' : '' }}"
                href="{{ route('courses.show', [$course, 'tab' => 'modules']) }}">
                {{ __('Topik & aktivitas') }}
                <span class="badge bg-primary-transparent ms-1">{{ (int) ($course->modules_count ?? 0) }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($activeTab ?? '') === 'peserta' ? 'active' : '' }}"
                href="{{ route('courses.show', [$course, 'tab' => 'peserta']) }}">
                {{ __('Peserta terdaftar') }}
                <span class="badge bg-primary-transparent ms-1">{{ $enrollmentsCount }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($activeTab ?? '') === 'forum' ? 'active' : '' }}"
                href="{{ route('courses.show', [$course, 'tab' => 'forum']) }}">
                {{ __('Forum') }}
                <span class="badge bg-primary-transparent ms-1">{{ (int) ($course->forum_threads_count ?? 0) }}</span>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade {{ ($activeTab ?? 'info') === 'info' ? 'show active' : '' }}" id="tab-info">
            @can('course edit')
                <div class="card custom-card">
                    <div class="card-header border-bottom-0 pb-0">
                        <span class="card-title mb-0">{{ __('Informasi kursus') }}</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('courses.update', $course) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            @include('courses.partials.form-fields', ['course' => $course])
                            <div class="mt-4 pt-3 border-top text-end">
                                <button type="submit" class="btn btn-primary btn-wave">
                                    <i class="ri-save-line me-1"></i>{{ __('Simpan perubahan') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card custom-card">
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ __('Kode') }}:</strong> {{ $course->kode }}</p>
                        <p class="mb-1"><strong>{{ __('Instruktur') }}:</strong> {{ $course->instruktur }}</p>
                        <p class="mb-0"><strong>{{ __('Deskripsi') }}:</strong> {{ $course->deskripsi }}</p>
                    </div>
                </div>
            @endcan
        </div>

        <div class="tab-pane fade {{ ($activeTab ?? '') === 'modules' ? 'show active' : '' }}" id="tab-modules">
            @if (($activeTab ?? '') === 'modules')
                @include('courses.partials.modules-manager', ['course' => $course])
            @endif
        </div>

        <div class="tab-pane fade {{ ($activeTab ?? '') === 'peserta' ? 'show active' : '' }}" id="tab-peserta">
            @if (($activeTab ?? '') === 'peserta')
            <div class="course-peserta row g-3">
                @can('course enrollment manage')
                    <div class="col-lg-4">
                        <div class="card custom-card course-peserta-enroll h-100">
                            <div class="card-header border-bottom-0 pb-0">
                                <span class="card-title mb-0">{{ __('Daftarkan peserta') }}</span>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('courses.enrollments.store', $course) }}" class="course-peserta-enroll__form">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label" for="enrollPesertaSelect">{{ __('Peserta') }}</label>
                                        <select name="user_id" id="enrollPesertaSelect"
                                            class="form-select @error('user_id') is-invalid @enderror"
                                            data-peserta-search-url="{{ route('courses.enrollments.available', $course) }}"
                                            data-placeholder="{{ __('Cari nama atau email...') }}"
                                            data-search-placeholder="{{ __('Ketik nama / email') }}"
                                            required>
                                            <option value="">{{ __('Cari nama atau email...') }}</option>
                                            @if (old('user_id'))
                                                @php
                                                    $oldUser = \App\Models\User::query()->find(old('user_id'));
                                                @endphp
                                                @if ($oldUser)
                                                    <option value="{{ $oldUser->id }}" selected>
                                                        {{ $oldUser->name }} ({{ $oldUser->email }})
                                                    </option>
                                                @endif
                                            @endif
                                        </select>
                                        @error('user_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        <div class="form-text">{{ __('Ketik untuk mencari, lalu pilih peserta.') }}</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-wave w-100">
                                        <i class="ri-user-add-line me-1"></i>{{ __('Daftarkan') }}
                                    </button>
                                </form>

                                <hr class="my-3">
                                <p class="text-muted fs-12 mb-2">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>{{ __('Atau import massal via NIP') }}
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('courses.enrollments.template', $course) }}"
                                        class="btn btn-sm btn-outline-secondary btn-wave">
                                        <i class="bi bi-file-earmark-excel me-1"></i>{{ __('Template') }}
                                    </a>
                                    <a href="{{ route('courses.enrollments.export', $course) }}"
                                        class="btn btn-sm btn-outline-success btn-wave">
                                        <i class="bi bi-download me-1"></i>{{ __('Export') }}
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-wave"
                                        data-bs-toggle="modal" data-bs-target="#enrollmentImportModal">
                                        <i class="bi bi-upload me-1"></i>{{ __('Import') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
                <div class="@can('course enrollment manage') col-lg-8 @else col-12 @endcan">
                    <div class="card custom-card course-peserta-filters mb-3">
                        <div class="card-body py-3">
                            <form method="GET" action="{{ route('courses.show', $course) }}" class="course-peserta-search course-peserta-search--panel">
                                <input type="hidden" name="tab" value="peserta">
                                <div class="course-peserta-search__filters">
                                    <div class="course-peserta-search__field">
                                        <label class="course-peserta-search__label" for="course-peserta-q">
                                            <i class="bi bi-search" aria-hidden="true"></i>
                                            <span>{{ __('Cari') }}</span>
                                        </label>
                                        <input type="search" name="q" id="course-peserta-q" value="{{ $pesertaSearch }}"
                                            class="form-control form-control-sm"
                                            placeholder="{{ __('Cari nama / email...') }}"
                                            aria-label="{{ __('Cari peserta') }}">
                                    </div>
                                    <div class="course-peserta-search__actions">
                                        <button type="submit" class="btn btn-sm btn-primary btn-wave">
                                            <i class="bi bi-funnel me-1"></i>{{ __('Terapkan') }}
                                        </button>
                                        <a href="{{ route('courses.show', [$course, 'tab' => 'peserta']) }}" class="btn btn-sm btn-light btn-wave">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset') }}
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card custom-card course-peserta-list">
                        <div class="card-header course-peserta-list__header border-bottom-0">
                            <div class="course-peserta-list__title">
                                <span class="card-title mb-0">{{ __('Daftar peserta') }}</span>
                                <span class="badge bg-primary-transparent">{{ $enrollments->total() }}</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @can('course enrollment manage')
                                <div class="course-peserta-bulkbar d-none" id="coursePesertaBulkbar">
                                    <div class="course-peserta-bulkbar__info">
                                        <span class="course-peserta-bulkbar__count" id="coursePesertaBulkCount">0</span>
                                        {{ __('peserta dipilih') }}
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger btn-wave" id="coursePesertaBulkDelete">
                                        <i class="ri-delete-bin-line me-1"></i>{{ __('Hapus terpilih') }}
                                    </button>
                                </div>
                                <form method="POST" action="{{ route('courses.enrollments.bulk-destroy', $course) }}"
                                    id="coursePesertaBulkForm" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                    <div id="coursePesertaBulkIds"></div>
                                </form>
                            @endcan
                            <div class="table-responsive">
                                <table class="table table-hover course-peserta-table mb-0 align-middle" id="coursePesertaTable">
                                    <thead>
                                        <tr>
                                            @can('course enrollment manage')
                                                <th class="course-peserta-table__check" scope="col">
                                                    <input type="checkbox" class="form-check-input" id="coursePesertaCheckAll"
                                                        aria-label="{{ __('Pilih semua') }}">
                                                </th>
                                            @endcan
                                            <th class="course-peserta-table__num" scope="col">#</th>
                                            <th scope="col">{{ __('Nama') }}</th>
                                            <th scope="col">{{ __('Surel') }}</th>
                                            <th scope="col">{{ __('Progres') }}</th>
                                            <th scope="col">{{ __('Status') }}</th>
                                            <th scope="col">{{ __('Topik') }}</th>
                                            @can('course enrollment manage')
                                                <th class="text-center" scope="col">{{ __('Aksi') }}</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($enrollments as $enrollment)
                                            <tr>
                                                @can('course enrollment manage')
                                                    <td class="course-peserta-table__check">
                                                        <input type="checkbox" class="form-check-input js-peserta-check"
                                                            value="{{ $enrollment->id }}"
                                                            aria-label="{{ __('Pilih :name', ['name' => $enrollment->user->name]) }}">
                                                    </td>
                                                @endcan
                                                <td class="course-peserta-table__num text-muted">
                                                    {{ $enrollments->firstItem() + $loop->index }}
                                                </td>
                                                <td>
                                                    <span class="fw-medium">{{ $enrollment->user->name }}</span>
                                                </td>
                                                <td class="text-muted">{{ $enrollment->user->email }}</td>
                                                <td style="min-width: 7rem">
                                                    <button type="button"
                                                        class="course-peserta-progress course-peserta-progress--clickable js-enrollment-progress"
                                                        data-progress-url="{{ route('courses.enrollments.progress', [$course, $enrollment]) }}"
                                                        data-participant-name="{{ $enrollment->user->name }}"
                                                        title="{{ __('Lihat detail progres') }}"
                                                        aria-label="{{ __('Lihat detail progres :name', ['name' => $enrollment->user->name]) }}">
                                                        <div class="progress" role="progressbar" aria-valuenow="{{ $enrollment->progress }}" aria-valuemin="0" aria-valuemax="100">
                                                            <div class="progress-bar" style="width: {{ min(100, (int) $enrollment->progress) }}%"></div>
                                                        </div>
                                                        <span class="course-peserta-progress__label">{{ $enrollment->progress }}%</span>
                                                        <i class="ri-eye-line course-peserta-progress__hint" aria-hidden="true"></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = match ($enrollment->status) {
                                                            'Selesai' => 'success',
                                                            'Berlangsung' => 'primary',
                                                            default => 'secondary',
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $statusClass }}-transparent">{{ __($enrollment->status) }}</span>
                                                </td>
                                                <td>
                                                    <button type="button"
                                                        class="course-peserta-topik-btn js-enrollment-progress"
                                                        data-progress-url="{{ route('courses.enrollments.progress', [$course, $enrollment]) }}"
                                                        data-participant-name="{{ $enrollment->user->name }}"
                                                        title="{{ __('Lihat topik & aktivitas') }}"
                                                        aria-label="{{ __('Lihat topik & aktivitas :name', ['name' => $enrollment->user->name]) }}">
                                                        <span class="course-peserta-topik-btn__frac">
                                                            {{ $enrollment->modul_selesai }} / {{ $course->modul_total }}
                                                        </span>
                                                        <span class="course-peserta-topik-btn__label">{{ __('Detail') }}</span>
                                                    </button>
                                                </td>
                                                @can('course enrollment manage')
                                                    <td class="text-center">
                                                        <form action="{{ route('courses.enrollments.destroy', [$course, $enrollment]) }}"
                                                            method="post" class="d-inline">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="button" class="btn btn-sm btn-danger-light btn-icon btn-wave js-delete-confirm"
                                                                title="{{ __('Cabut pendaftaran') }}">
                                                                <i class="ri-user-unfollow-line"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                @endcan
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ auth()->user()?->can('course enrollment manage') ? 8 : 6 }}" class="text-center text-muted py-5">
                                                    <i class="ri-user-search-line d-block fs-24 mb-2 opacity-50"></i>
                                                    @if ($pesertaSearch !== '')
                                                        {{ __('Tidak ada peserta yang cocok dengan pencarian.') }}
                                                    @else
                                                        {{ __('Belum ada peserta terdaftar.') }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if ($enrollments->hasPages() || $enrollments->total() > 0)
                            <div class="card-footer course-peserta-list__footer">
                                <span class="text-muted fs-12">
                                    {{ __('Menampilkan :from–:to dari :total peserta', [
                                        'from' => $enrollments->firstItem() ?? 0,
                                        'to' => $enrollments->lastItem() ?? 0,
                                        'total' => $enrollments->total(),
                                    ]) }}
                                </span>
                                <div class="course-peserta-list__pager">
                                    {{ $enrollments->appends(['tab' => 'peserta'])->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @can('course enrollment manage')
                <div class="modal fade" id="enrollmentImportModal" tabindex="-1"
                    aria-labelledby="enrollmentImportModalTitle" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('courses.enrollments.import', $course) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="enrollmentImportModalTitle">{{ __('Import peserta via NIP') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="enrollmentImportFile" class="form-label">{{ __('File import') }}</label>
                                        <input type="file" name="import_file" id="import_file"
                                            class="form-control @error('import_file') is-invalid @enderror"
                                            accept=".xlsx,.xls,.csv" required>
                                        <div class="form-text">
                                            {{ __('Gunakan template yang diunduh, isi kolom NIP. Hanya file Excel (.xlsx, .xls) atau CSV yang didukung.') }}
                                        </div>
                                        @error('import_file')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                                    <button type="submit" class="btn btn-primary btn-wave">
                                        <i class="bi bi-upload me-1"></i>{{ __('Import') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan

            <div class="modal fade enrollment-progress-modal" id="enrollmentProgressModal" tabindex="-1"
                aria-labelledby="enrollmentProgressModalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content enrollment-progress-modal__content">
                        <div class="modal-header enrollment-progress-modal__header border-0">
                            <div class="enrollment-progress-modal__heading">
                                <span class="enrollment-progress-modal__kicker">{{ __('Detail progres') }}</span>
                                <h5 class="modal-title" id="enrollmentProgressModalTitle">—</h5>
                                <p class="enrollment-progress-modal__email mb-0" id="enrollmentProgressModalEmail"></p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                        </div>
                        <div class="enrollment-progress-modal__stats" id="enrollmentProgressModalStats" hidden></div>
                        <div class="modal-body enrollment-progress-modal__body" id="enrollmentProgressModalBody">
                            <div class="enrollment-progress-modal__state" data-state="idle">
                                <div class="enrollment-progress-modal__spinner" role="status" aria-live="polite">
                                    <span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>
                                    <span>{{ __('Memuat detail progres...') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer enrollment-progress-modal__footer border-0">
                            <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="tab-pane fade {{ ($activeTab ?? '') === 'forum' ? 'show active' : '' }}" id="tab-forum">
            @if (($activeTab ?? '') === 'forum')
                @include('courses.partials.forum-manager', [
                    'course' => $course,
                    'forumThreads' => $forumThreads,
                    'likeSummary' => $likeSummary ?? ['threads' => [], 'replies' => []],
                ])
            @endif
        </div>
    </div>
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('backend/assets/css/admin-topics.css') }}?v={{ @filemtime(public_path('backend/assets/css/admin-topics.css')) ?: time() }}">
@endpush

@push('scripts')
    @if (($activeTab ?? '') === 'modules')
        <script>
            window.activityFilePreviewI18n = {
                preview: @json(__('Preview')),
                openTab: @json(__('Buka di tab baru')),
                loading: @json(__('Memuat preview...')),
                unsupported: @json(__('Preview tidak tersedia untuk tipe :ext. Gunakan tombol buka di tab baru.')),
            };
            window.activityFormI18n = {
                add: @json(__('Tambah')),
                edit: @json(__('Ubah')),
                save: @json(__('Simpan')),
                preview: @json(__('Preview')),
                openTab: @json(__('Tab baru')),
                uploadFile: @json(__('Unggah berkas')),
                instructionFile: @json(__('Berkas instruksi')),
                instructionHelp: @json(__('Opsional. Word, PowerPoint, PDF, atau ZIP — materi/instruksi untuk peserta.')),
            };
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
        <script src="{{ asset('backend/assets/js/admin-topics.js') }}?v={{ @filemtime(public_path('backend/assets/js/admin-topics.js')) ?: time() }}"></script>
    @endif
    <script src="{{ asset('backend/assets/js/admin-course-form.js') }}?v={{ @filemtime(public_path('backend/assets/js/admin-course-form.js')) ?: time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // AJAX search for "Daftarkan peserta"
            var enrollSelect = document.getElementById('enrollPesertaSelect');
            if (enrollSelect && typeof Choices !== 'undefined') {
                var searchUrl = enrollSelect.getAttribute('data-peserta-search-url') || '';
                enrollSelect.dataset.choicesInit = '1';
                var enrollChoices = new Choices(enrollSelect, {
                    allowHTML: false,
                    searchEnabled: true,
                    searchChoices: false,
                    searchPlaceholderValue: enrollSelect.getAttribute('data-search-placeholder') || 'Cari...',
                    itemSelectText: '',
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: enrollSelect.getAttribute('data-placeholder') || 'Pilih...',
                    noResultsText: @json(__('Tidak ditemukan')),
                    noChoicesText: @json(__('Ketik untuk mencari peserta')),
                });

                var searchTimer = null;
                var lastQuery = null;

                function loadPeserta(query) {
                    if (!searchUrl) return;
                    var q = String(query || '').trim();
                    if (q === lastQuery) return;
                    lastQuery = q;

                    var url = searchUrl + (searchUrl.indexOf('?') >= 0 ? '&' : '?') + 'q=' + encodeURIComponent(q);
                    fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    })
                        .then(function (res) { return res.ok ? res.json() : { results: [] }; })
                        .then(function (payload) {
                            var results = Array.isArray(payload.results) ? payload.results : [];
                            enrollChoices.setChoices(results, 'value', 'label', true);
                        })
                        .catch(function () { /* ignore */ });
                }

                enrollSelect.addEventListener('search', function (event) {
                    var q = event.detail && event.detail.value != null ? event.detail.value : '';
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function () { loadPeserta(q); }, 280);
                });

                var searchInput = enrollSelect.closest('.choices')
                    ? enrollSelect.closest('.choices').querySelector('.choices__input--cloned, .choices__input')
                    : null;
                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        var q = searchInput.value || '';
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(function () { loadPeserta(q); }, 280);
                    });
                }

                // Prefill on open / empty query
                loadPeserta('');
            }

            var checkAll = document.getElementById('coursePesertaCheckAll');
            var bulkBar = document.getElementById('coursePesertaBulkbar');
            var bulkCount = document.getElementById('coursePesertaBulkCount');
            var bulkDeleteBtn = document.getElementById('coursePesertaBulkDelete');
            var bulkForm = document.getElementById('coursePesertaBulkForm');
            var bulkIds = document.getElementById('coursePesertaBulkIds');
            var table = document.getElementById('coursePesertaTable');

            if (table && checkAll && bulkBar && bulkForm) {
                function rowChecks() {
                    return Array.prototype.slice.call(table.querySelectorAll('.js-peserta-check'));
                }

                function selectedChecks() {
                    return rowChecks().filter(function (el) { return el.checked; });
                }

                function syncBulkUi() {
                    var all = rowChecks();
                    var selected = selectedChecks();
                    var n = selected.length;

                    bulkCount.textContent = String(n);
                    bulkBar.classList.toggle('d-none', n === 0);

                    if (all.length === 0) {
                        checkAll.checked = false;
                        checkAll.indeterminate = false;
                        return;
                    }

                    checkAll.checked = n === all.length;
                    checkAll.indeterminate = n > 0 && n < all.length;
                }

                checkAll.addEventListener('change', function () {
                    var on = checkAll.checked;
                    rowChecks().forEach(function (el) { el.checked = on; });
                    syncBulkUi();
                });

                table.addEventListener('change', function (e) {
                    if (e.target && e.target.classList.contains('js-peserta-check')) {
                        syncBulkUi();
                    }
                });

                if (bulkDeleteBtn) {
                    bulkDeleteBtn.addEventListener('click', function () {
                        var selected = selectedChecks();
                        if (selected.length === 0) {
                            return;
                        }

                        var confirmOpts = {
                            title: @json(__('Hapus peserta terpilih?')),
                            text: @json(__('Pendaftaran peserta yang dipilih akan dicabut dari kursus ini. Tindakan ini tidak dapat dibatalkan.')),
                            confirmButtonText: @json(__('Ya, hapus')),
                        };

                        function submitBulk() {
                            bulkIds.innerHTML = '';
                            selected.forEach(function (el) {
                                var input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'enrollment_ids[]';
                                input.value = el.value;
                                bulkIds.appendChild(input);
                            });
                            bulkForm.submit();
                        }

                        if (window.BpkpSwal && typeof window.BpkpSwal.confirmDanger === 'function') {
                            window.BpkpSwal.confirmDanger(confirmOpts).then(function (result) {
                                if (result.isConfirmed) {
                                    submitBulk();
                                }
                            });
                            return;
                        }

                        if (window.confirm(confirmOpts.title + '\n\n' + confirmOpts.text)) {
                            submitBulk();
                        }
                    });
                }

                syncBulkUi();
            }

            // Modal detail topik & aktivitas per peserta
            var progressModalEl = document.getElementById('enrollmentProgressModal');
            if (progressModalEl && typeof bootstrap !== 'undefined') {
                var progressModal = bootstrap.Modal.getOrCreateInstance(progressModalEl);
                var titleEl = document.getElementById('enrollmentProgressModalTitle');
                var emailEl = document.getElementById('enrollmentProgressModalEmail');
                var statsEl = document.getElementById('enrollmentProgressModalStats');
                var bodyEl = document.getElementById('enrollmentProgressModalBody');
                var progressFetchCtrl = null;

                var i18n = {
                    loading: @json(__('Memuat detail progres...')),
                    error: @json(__('Gagal memuat detail progres. Silakan coba lagi.')),
                    retry: @json(__('Coba lagi')),
                    topic: @json(__('Topik')),
                    progress: @json(__('Progres')),
                    status: @json(__('Status')),
                    activities: @json(__('Aktivitas')),
                    completed: @json(__('Selesai')),
                    incomplete: @json(__('Belum selesai')),
                    required: @json(__('Wajib')),
                    optional: @json(__('Opsional')),
                    empty: @json(__('Belum ada topik atau aktivitas pada kursus ini.')),
                    completedAt: @json(__('Diselesaikan')),
                    of: @json(__(':done dari :total')),
                };

                function escapeHtml(value) {
                    return String(value == null ? '' : value)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                }

                function setLoading() {
                    statsEl.hidden = true;
                    statsEl.innerHTML = '';
                    bodyEl.innerHTML =
                        '<div class="enrollment-progress-modal__state" data-state="loading">' +
                            '<div class="enrollment-progress-modal__spinner" role="status" aria-live="polite">' +
                                '<span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>' +
                                '<span>' + escapeHtml(i18n.loading) + '</span>' +
                            '</div>' +
                        '</div>';
                }

                function setError(url) {
                    statsEl.hidden = true;
                    statsEl.innerHTML = '';
                    bodyEl.innerHTML =
                        '<div class="enrollment-progress-modal__state" data-state="error">' +
                            '<div class="enrollment-progress-modal__error">' +
                                '<i class="ri-error-warning-line" aria-hidden="true"></i>' +
                                '<p class="mb-2">' + escapeHtml(i18n.error) + '</p>' +
                                '<button type="button" class="btn btn-sm btn-primary btn-wave js-progress-retry" data-url="' + escapeHtml(url) + '">' +
                                    escapeHtml(i18n.retry) +
                                '</button>' +
                            '</div>' +
                        '</div>';
                }

                function statusClass(status) {
                    if (status === 'Selesai') return 'success';
                    if (status === 'Berlangsung') return 'primary';
                    return 'secondary';
                }

                function renderBreakdown(data) {
                    var progress = Math.max(0, Math.min(100, parseInt(data.progress, 10) || 0));
                    var modules = Array.isArray(data.modules) ? data.modules : [];

                    statsEl.hidden = false;
                    statsEl.innerHTML =
                        '<div class="enrollment-progress-stat">' +
                            '<span class="enrollment-progress-stat__label">' + escapeHtml(i18n.progress) + '</span>' +
                            '<div class="enrollment-progress-stat__meter">' +
                                '<div class="progress"><div class="progress-bar" style="width:' + progress + '%"></div></div>' +
                                '<strong>' + progress + '%</strong>' +
                            '</div>' +
                        '</div>' +
                        '<div class="enrollment-progress-stat">' +
                            '<span class="enrollment-progress-stat__label">' + escapeHtml(i18n.status) + '</span>' +
                            '<span class="badge bg-' + statusClass(data.status) + '-transparent">' + escapeHtml(data.status_label || data.status || '—') + '</span>' +
                        '</div>' +
                        '<div class="enrollment-progress-stat">' +
                            '<span class="enrollment-progress-stat__label">' + escapeHtml(i18n.topic) + '</span>' +
                            '<strong>' + escapeHtml((data.modules_done || 0) + ' / ' + (data.modules_total || 0)) + '</strong>' +
                        '</div>' +
                        '<div class="enrollment-progress-stat">' +
                            '<span class="enrollment-progress-stat__label">' + escapeHtml(i18n.activities) + '</span>' +
                            '<strong>' + escapeHtml((data.activities_done || 0) + ' / ' + (data.activities_total || 0)) + '</strong>' +
                        '</div>';

                    if (modules.length === 0) {
                        bodyEl.innerHTML =
                            '<div class="enrollment-progress-modal__state" data-state="empty">' +
                                '<p class="mb-0 text-muted">' + escapeHtml(i18n.empty) + '</p>' +
                            '</div>';
                        return;
                    }

                    var html = '<div class="enrollment-progress-topics">';
                    modules.forEach(function (mod) {
                        var done = !!mod.is_complete;
                        var activities = Array.isArray(mod.activities) ? mod.activities : [];
                        html +=
                            '<section class="enrollment-progress-topic' + (done ? ' is-done' : '') + '">' +
                                '<header class="enrollment-progress-topic__head">' +
                                    '<div class="enrollment-progress-topic__title-wrap">' +
                                        '<span class="enrollment-progress-topic__num">' + escapeHtml(mod.urutan) + '</span>' +
                                        '<div>' +
                                            '<span class="enrollment-progress-topic__kicker">' + escapeHtml(i18n.topic) + ' ' + escapeHtml(mod.urutan) + '</span>' +
                                            '<h6 class="enrollment-progress-topic__title">' + escapeHtml(mod.title || '—') + '</h6>' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="enrollment-progress-topic__meta">' +
                                        '<span class="badge bg-' + (done ? 'success' : 'secondary') + '-transparent">' +
                                            escapeHtml(done ? i18n.completed : i18n.incomplete) +
                                        '</span>' +
                                        '<span class="enrollment-progress-topic__count">' +
                                            escapeHtml(String(mod.done_count || 0) + ' / ' + String(mod.total_count || 0)) +
                                        '</span>' +
                                    '</div>' +
                                '</header>' +
                                '<ul class="enrollment-progress-activities">';

                        if (activities.length === 0) {
                            html += '<li class="enrollment-progress-activity is-empty text-muted">' + escapeHtml(i18n.empty) + '</li>';
                        } else {
                            activities.forEach(function (act) {
                                var actDone = !!act.is_complete;
                                html +=
                                    '<li class="enrollment-progress-activity' + (actDone ? ' is-done' : '') + '">' +
                                        '<span class="enrollment-progress-activity__icon" aria-hidden="true">' +
                                            '<i class="bi ' + escapeHtml(act.icon || 'bi-circle') + '"></i>' +
                                        '</span>' +
                                        '<div class="enrollment-progress-activity__body">' +
                                            '<span class="enrollment-progress-activity__title">' + escapeHtml(act.title || '—') + '</span>' +
                                            '<span class="enrollment-progress-activity__meta">' +
                                                '<span class="enrollment-progress-activity__type">' + escapeHtml(act.type_label || act.type || '') + '</span>' +
                                                (act.is_required
                                                    ? '<span class="text-muted">· ' + escapeHtml(i18n.required) + '</span>'
                                                    : '<span class="text-muted">· ' + escapeHtml(i18n.optional) + '</span>') +
                                                (actDone && act.completed_at
                                                    ? '<span class="text-muted">· ' + escapeHtml(i18n.completedAt) + ' ' + escapeHtml(act.completed_at) + '</span>'
                                                    : '') +
                                            '</span>' +
                                        '</div>' +
                                        '<span class="enrollment-progress-activity__state' + (actDone ? ' is-done' : '') + '" title="' + escapeHtml(actDone ? i18n.completed : i18n.incomplete) + '">' +
                                            (actDone
                                                ? '<i class="ri-checkbox-circle-fill" aria-hidden="true"></i>'
                                                : '<i class="ri-checkbox-blank-circle-line" aria-hidden="true"></i>') +
                                        '</span>' +
                                    '</li>';
                            });
                        }

                        html += '</ul></section>';
                    });
                    html += '</div>';
                    bodyEl.innerHTML = html;
                }

                function loadProgress(url, nameHint) {
                    if (!url) return;

                    if (progressFetchCtrl) {
                        progressFetchCtrl.abort();
                    }
                    progressFetchCtrl = new AbortController();

                    if (nameHint) {
                        titleEl.textContent = nameHint;
                    }
                    emailEl.textContent = '';
                    setLoading();
                    progressModal.show();

                    fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                        signal: progressFetchCtrl.signal,
                    })
                        .then(function (res) {
                            if (!res.ok) throw new Error('bad_status');
                            return res.json();
                        })
                        .then(function (data) {
                            if (data.participant) {
                                titleEl.textContent = data.participant.name || nameHint || '—';
                                emailEl.textContent = data.participant.email || '';
                            }
                            renderBreakdown(data);
                        })
                        .catch(function (err) {
                            if (err && err.name === 'AbortError') return;
                            setError(url);
                        });
                }

                document.addEventListener('click', function (e) {
                    var trigger = e.target.closest('.js-enrollment-progress');
                    if (!trigger) return;
                    e.preventDefault();
                    loadProgress(
                        trigger.getAttribute('data-progress-url'),
                        trigger.getAttribute('data-participant-name') || ''
                    );
                });

                bodyEl.addEventListener('click', function (e) {
                    var retry = e.target.closest('.js-progress-retry');
                    if (!retry) return;
                    loadProgress(retry.getAttribute('data-url'), titleEl.textContent || '');
                });
            }
        });
    </script>
@endpush
