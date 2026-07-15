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
                    {{ __('Daftar pendaftaran peserta di seluruh kursus.') }}
                </p>
            </div>
            @can('course view')
                <a href="{{ route('courses.index') }}" class="btn btn-outline-primary btn-wave">
                    <i class="bi bi-mortarboard me-1"></i>{{ __('Kelola kursus') }}
                </a>
            @endcan
        </div>

        <x-alert />

        <div class="admin-participants-stats">
            <article class="admin-participants-stat">
                <span class="admin-participants-stat__icon"><i class="bi bi-people"></i></span>
                <div>
                    <p class="admin-participants-stat__label">{{ __('Akun peserta') }}</p>
                    <p class="admin-participants-stat__value">{{ number_format($stats['peserta']) }}</p>
                </div>
            </article>
            <article class="admin-participants-stat">
                <span class="admin-participants-stat__icon admin-participants-stat__icon--teal"><i class="bi bi-person-check"></i></span>
                <div>
                    <p class="admin-participants-stat__label">{{ __('Total pendaftaran') }}</p>
                    <p class="admin-participants-stat__value">{{ number_format($stats['enrollments']) }}</p>
                </div>
            </article>
            <article class="admin-participants-stat">
                <span class="admin-participants-stat__icon admin-participants-stat__icon--amber"><i class="bi bi-journal-bookmark"></i></span>
                <div>
                    <p class="admin-participants-stat__label">{{ __('Kursus aktif') }}</p>
                    <p class="admin-participants-stat__value">{{ number_format($stats['courses']) }}</p>
                </div>
            </article>
            <article class="admin-participants-stat">
                <span class="admin-participants-stat__icon admin-participants-stat__icon--rose"><i class="bi bi-graph-up"></i></span>
                <div>
                    <p class="admin-participants-stat__label">{{ __('Rata-rata progres') }}</p>
                    <p class="admin-participants-stat__value">{{ $stats['avg_progress'] }}%</p>
                </div>
            </article>
        </div>

        <div class="admin-participants-panel">
            <form method="GET" action="{{ route('participants.index') }}" class="admin-participants-filters">
                <div class="admin-participants-filters__search">
                    <label class="admin-participants-filters__label" for="participant-search">{{ __('Cari') }}</label>
                    <div class="admin-participants-filters__search-field">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" name="q" id="participant-search" value="{{ $filters['q'] }}"
                            class="form-control" placeholder="{{ __('Nama atau email peserta...') }}">
                    </div>
                </div>
                <div class="admin-participants-filters__item">
                    <label class="admin-participants-filters__label" for="participant-course">{{ __('Kursus') }}</label>
                    <select name="course_id" id="participant-course" class="form-select" data-trigger
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
                <div class="admin-participants-filters__item">
                    <label class="admin-participants-filters__label" for="participant-status">{{ __('Status') }}</label>
                    <select name="status" id="participant-status" class="form-select">
                        <option value="">{{ __('Semua status') }}</option>
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}" @selected($filters['status'] === $statusOption)>
                                {{ $statusOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-participants-filters__actions">
                    <button type="submit" class="btn btn-primary btn-wave">
                        <i class="bi bi-funnel me-1"></i>{{ __('Terapkan') }}
                    </button>
                    @if ($filters['q'] !== '' || $filters['course_id'] !== '' || $filters['status'] !== '')
                        <a href="{{ route('participants.index') }}" class="btn btn-light btn-wave">{{ __('Reset') }}</a>
                    @endif
                </div>
            </form>

            <div class="admin-participants-table-wrap">
                <div class="table-responsive">
                    <table class="table admin-participants-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th scope="col" class="admin-participants-table__num">#</th>
                                <th scope="col">{{ __('Peserta') }}</th>
                                <th scope="col">{{ __('Kursus') }}</th>
                                <th scope="col">{{ __('Progres') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                                <th scope="col">{{ __('Terdaftar') }}</th>
                                <th scope="col" class="text-end">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($enrollments as $enrollment)
                                @php
                                    $user = $enrollment->user;
                                    $course = $enrollment->course;
                                    $progress = max(0, min(100, (int) $enrollment->progress));
                                @endphp
                                <tr>
                                    <td class="admin-participants-table__num text-muted">
                                        {{ $enrollments->firstItem() + $loop->index }}
                                    </td>
                                    <td>
                                        <div class="admin-participants-person">
                                            <span class="admin-participants-person__avatar" aria-hidden="true">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user?->name ?? '?', 0, 1)) }}
                                            </span>
                                            <div class="min-w-0">
                                                <div class="admin-participants-person__name text-truncate">{{ $user?->name ?? '—' }}</div>
                                                <div class="admin-participants-person__email text-truncate">{{ $user?->email ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($course)
                                            <a href="{{ route('courses.show', [$course, 'tab' => 'peserta']) }}"
                                                class="admin-participants-course">
                                                <span class="admin-participants-course__title">{{ $course->judul }}</span>
                                                <span class="admin-participants-course__meta">
                                                    {{ $course->kode }}
                                                    @if ($course->kategori)
                                                        · {{ $course->kategori }}
                                                    @endif
                                                </span>
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 9rem">
                                        <div class="admin-participants-progress">
                                            <div class="progress" role="progressbar" aria-valuenow="{{ $progress }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar" style="width: {{ $progress }}%"></div>
                                            </div>
                                            <span class="admin-participants-progress__label">{{ $progress }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match (strtolower((string) $enrollment->status)) {
                                                'selesai', 'completed' => 'is-done',
                                                'berlangsung', 'in_progress', 'ongoing' => 'is-active',
                                                default => 'is-muted',
                                            };
                                        @endphp
                                        <span class="admin-participants-status {{ $statusClass }}">
                                            {{ $enrollment->status ?: '—' }}
                                        </span>
                                    </td>
                                    <td class="text-muted fs-12 text-nowrap">
                                        {{ optional($enrollment->enrolled_at)->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @if ($course)
                                            <a href="{{ route('courses.show', [$course, 'tab' => 'peserta']) }}"
                                                class="btn btn-sm btn-light btn-wave" title="{{ __('Buka di kursus') }}">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                            @can('course enrollment manage')
                                                <form action="{{ route('courses.enrollments.destroy', [$course, $enrollment]) }}"
                                                    method="post" class="d-inline">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="button" class="btn btn-sm btn-danger-light btn-wave js-delete-confirm"
                                                        title="{{ __('Hapus pendaftaran') }}">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
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

            @if ($enrollments->hasPages() || $enrollments->total() > 0)
                <div class="admin-participants-footer">
                    <span class="text-muted fs-12">
                        {{ __('Menampilkan :from–:to dari :total pendaftaran', [
                            'from' => $enrollments->firstItem() ?? 0,
                            'to' => $enrollments->lastItem() ?? 0,
                            'total' => $enrollments->total(),
                        ]) }}
                    </span>
                    <div class="admin-participants-footer__pager">
                        {{ $enrollments->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('backend/assets/css/admin-participants.css') }}?v={{ @filemtime(public_path('backend/assets/css/admin-participants.css')) ?: time() }}">
@endpush
