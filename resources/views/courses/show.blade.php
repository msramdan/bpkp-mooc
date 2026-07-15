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
                <div class="admin-course-detail__meta">
                    <span class="admin-course-detail__chip">{{ $course->kode }}</span>
                    @if ($course->kategori)
                        <span class="admin-course-detail__chip admin-course-detail__chip--primary">
                            <i class="bi bi-folder2-open"></i>{{ $course->kategori }}
                        </span>
                    @endif
                    @if ($course->is_published)
                        <span class="admin-course-detail__chip admin-course-detail__chip--ok">
                            <i class="bi bi-broadcast"></i>{{ __('Di katalog') }}
                        </span>
                    @else
                        <span class="admin-course-detail__chip admin-course-detail__chip--muted">
                            <i class="bi bi-eye-slash"></i>{{ __('Draft') }}
                        </span>
                    @endif
                    @foreach ($course->tags as $tag)
                        <span class="admin-course-detail__chip">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
            @can('course delete')
                <form action="{{ route('courses.destroy', $course) }}" method="post" class="d-inline">
                    @csrf
                    @method('delete')
                    <button type="button" class="btn btn-sm btn-danger-light btn-wave js-delete-confirm">
                        <i class="ri-delete-bin-line me-1"></i>{{ __('Hapus kursus') }}
                    </button>
                </form>
            @endcan
        </div>

        <x-alert />

        <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link {{ ($activeTab ?? 'info') === 'info' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-info" type="button">
                {{ __('Informasi kursus') }}
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ ($activeTab ?? '') === 'modules' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-modules" type="button">
                {{ __('Topik & aktivitas') }}
                <span class="badge bg-primary-transparent ms-1">{{ $course->modules->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ ($activeTab ?? '') === 'peserta' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-peserta" type="button">
                {{ __('Peserta terdaftar') }}
                <span class="badge bg-primary-transparent ms-1">{{ $enrollmentsCount }}</span>
            </button>
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
            @include('courses.partials.modules-manager', ['course' => $course])
        </div>

        <div class="tab-pane fade {{ ($activeTab ?? '') === 'peserta' ? 'show active' : '' }}" id="tab-peserta">
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
                                            data-trigger
                                            data-placeholder="{{ __('Cari nama atau email...') }}"
                                            data-search-placeholder="{{ __('Ketik nama / email') }}"
                                            required>
                                            <option value="">{{ __('Cari nama atau email...') }}</option>
                                            @foreach ($pesertaUsers as $user)
                                                @if (! $enrolledUserIds->contains($user->id))
                                                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                                        {{ $user->name }} ({{ $user->email }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('user_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        <div class="form-text">{{ __('Ketik untuk mencari, lalu pilih peserta.') }}</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-wave w-100">
                                        <i class="ri-user-add-line me-1"></i>{{ __('Daftarkan') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endcan
                <div class="@can('course enrollment manage') col-lg-8 @else col-12 @endcan">
                    <div class="card custom-card course-peserta-list h-100">
                        <div class="card-header course-peserta-list__header border-bottom-0">
                            <div class="course-peserta-list__title">
                                <span class="card-title mb-0">{{ __('Daftar peserta') }}</span>
                                <span class="badge bg-primary-transparent">{{ $enrollments->total() }}</span>
                            </div>
                            <form method="GET" action="{{ route('courses.show', $course) }}" class="course-peserta-search">
                                <input type="hidden" name="tab" value="peserta">
                                <div class="input-group input-group-sm course-peserta-search__group">
                                    <span class="input-group-text"><i class="ri-search-line"></i></span>
                                    <input type="search" name="q" value="{{ $pesertaSearch }}"
                                        class="form-control"
                                        placeholder="{{ __('Cari nama / email...') }}"
                                        aria-label="{{ __('Cari peserta') }}">
                                    <button type="submit" class="btn btn-primary btn-wave">{{ __('Cari') }}</button>
                                    @if ($pesertaSearch !== '')
                                        <a href="{{ route('courses.show', [$course, 'tab' => 'peserta']) }}" class="btn btn-light btn-wave">{{ __('Reset') }}</a>
                                    @endif
                                </div>
                            </form>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover course-peserta-table mb-0 align-middle">
                                    <thead>
                                        <tr>
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
                                                <td class="course-peserta-table__num text-muted">
                                                    {{ $enrollments->firstItem() + $loop->index }}
                                                </td>
                                                <td>
                                                    <span class="fw-medium">{{ $enrollment->user->name }}</span>
                                                </td>
                                                <td class="text-muted">{{ $enrollment->user->email }}</td>
                                                <td style="min-width: 7rem">
                                                    <div class="course-peserta-progress">
                                                        <div class="progress" role="progressbar" aria-valuenow="{{ $enrollment->progress }}" aria-valuemin="0" aria-valuemax="100">
                                                            <div class="progress-bar" style="width: {{ min(100, (int) $enrollment->progress) }}%"></div>
                                                        </div>
                                                        <span class="course-peserta-progress__label">{{ $enrollment->progress }}%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = match ($enrollment->status) {
                                                            'Selesai' => 'success',
                                                            'Berlangsung' => 'primary',
                                                            default => 'secondary',
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $statusClass }}-transparent">{{ $enrollment->status }}</span>
                                                </td>
                                                <td>{{ $enrollment->modul_selesai }} / {{ $course->modul_total }}</td>
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
                                                <td colspan="7" class="text-center text-muted py-5">
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
        </div>
    </div>
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('backend/assets/css/admin-topics.css') }}?v={{ @filemtime(public_path('backend/assets/css/admin-topics.css')) ?: time() }}">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="{{ asset('backend/assets/js/admin-topics.js') }}?v={{ @filemtime(public_path('backend/assets/js/admin-topics.js')) ?: time() }}"></script>
    <script src="{{ asset('backend/assets/js/admin-course-form.js') }}?v={{ @filemtime(public_path('backend/assets/js/admin-course-form.js')) ?: time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tabMap = {
                '#tab-info': 'info',
                '#tab-modules': 'modules',
                '#tab-peserta': 'peserta'
            };

            document.querySelectorAll('[data-bs-toggle="tab"][data-bs-target]').forEach(function (btn) {
                btn.addEventListener('shown.bs.tab', function (event) {
                    var target = event.target.getAttribute('data-bs-target');
                    var tab = tabMap[target];
                    if (!tab) return;

                    var url = new URL(window.location.href);
                    url.searchParams.set('tab', tab);
                    if (tab !== 'peserta') {
                        url.searchParams.delete('q');
                        url.searchParams.delete('page');
                    }
                    window.history.replaceState({}, '', url);
                });
            });
        });
    </script>
@endpush
