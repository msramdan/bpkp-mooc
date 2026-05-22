@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Dashboard') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active" aria-current="page">{{ __('Ringkasan MOOC') }}</li>
            </ol>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card custom-card">
                <div class="card-body">
                    <span class="text-muted fs-12 d-block mb-1">{{ __('Total kursus') }}</span>
                    <h3 class="fw-semibold mb-0">{{ $stats['courses'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card custom-card">
                <div class="card-body">
                    <span class="text-muted fs-12 d-block mb-1">{{ __('Dipublikasikan') }}</span>
                    <h3 class="fw-semibold mb-0 text-success">{{ $stats['published'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card custom-card">
                <div class="card-body">
                    <span class="text-muted fs-12 d-block mb-1">{{ __('Pendaftaran') }}</span>
                    <h3 class="fw-semibold mb-0">{{ $stats['enrollments'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card custom-card">
                <div class="card-body">
                    <span class="text-muted fs-12 d-block mb-1">{{ __('Peserta') }}</span>
                    <h3 class="fw-semibold mb-0">{{ $stats['peserta'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span class="card-title mb-0">{{ __('Kursus terbaru') }}</span>
            @can('course view')
                <a href="{{ route('courses.index') }}" class="fs-12 text-primary">{{ __('Kelola kursus') }}</a>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Kode') }}</th>
                            <th>{{ __('Judul') }}</th>
                            <th>{{ __('Peserta') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentCourses as $course)
                            <tr>
                                <td>{{ $course->kode }}</td>
                                <td>{{ $course->judul }}</td>
                                <td>{{ $course->enrollments_count }}</td>
                                <td>
                                    @if ($course->is_published)
                                        <span class="badge bg-success-transparent">{{ __('Dipublikasikan') }}</span>
                                    @else
                                        <span class="badge bg-secondary-transparent">{{ __('Draft') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-light btn-wave">
                                        {{ __('Detail') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('Belum ada kursus.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
