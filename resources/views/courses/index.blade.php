@extends('layouts.app')

@section('title', __('Kursus'))

@section('content')
    <div class="admin-courses-page">
        <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Kursus') }}</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Kursus') }}</li>
                </ol>
            </div>
            @can('course create')
                <button type="button" class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#courseCreateModal">
                    <i class="ri-add-line align-middle me-1"></i>{{ __('Tambah kursus') }}
                </button>
            @endcan
        </div>

        <x-alert />

        <div class="admin-course-panel admin-course-panel--filters">
            <div class="admin-course-filters" role="group" aria-label="{{ __('Filter kursus') }}">
                <div class="admin-course-filters__item">
                    <label class="admin-course-filters__label" for="filter-kategori">
                        <i class="bi bi-folder2-open" aria-hidden="true"></i>
                        <span>{{ __('Kategori') }}</span>
                    </label>
                    <select id="filter-kategori" class="form-select form-select-sm admin-course-filters__select">
                        <option value="">{{ __('Semua kategori') }}</option>
                        @foreach ($learningCategories as $categoryName)
                            <option value="{{ $categoryName }}">{{ $categoryName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-course-filters__item">
                    <label class="admin-course-filters__label" for="filter-published">
                        <i class="bi bi-broadcast" aria-hidden="true"></i>
                        <span>{{ __('Katalog peserta') }}</span>
                    </label>
                    <select id="filter-published" class="form-select form-select-sm admin-course-filters__select">
                        <option value="">{{ __('Semua') }}</option>
                        <option value="1">{{ __('Ya') }}</option>
                        <option value="0">{{ __('Tidak') }}</option>
                    </select>
                </div>
                <div class="admin-course-filters__actions">
                    <button type="button" id="filter-courses-apply" class="btn btn-sm btn-primary btn-wave">
                        <i class="bi bi-funnel me-1"></i>{{ __('Terapkan') }}
                    </button>
                    <button type="button" id="filter-courses-reset" class="btn btn-sm btn-light btn-wave" title="{{ __('Reset filter') }}">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="admin-course-panel admin-course-panel--list">
            <div class="admin-course-list-toolbar">
                <div class="admin-course-dt-top">
                    <div id="courses-length"></div>
                    <div id="courses-search"></div>
                </div>
            </div>

            <div id="courses-grid" class="admin-courses-grid">
                <div class="admin-courses-grid__loading">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ __('Memuat kursus...') }}
                </div>
            </div>

            <div id="courses-grid-empty" class="admin-courses-empty d-none">
                <i class="bi bi-journal-x"></i>
                <p>{{ __('Tidak ada kursus yang cocok dengan pencarian.') }}</p>
            </div>

            <div class="admin-course-list-footer">
                <div class="admin-course-dt-bottom">
                    <p class="admin-course-toolbar__info" id="courses-info"></p>
                    <div class="admin-course-pagination" id="courses-pagination"></div>
                </div>
            </div>
        </div>

        {{-- Host DataTables di luar alur layout agar tidak membuat celah kosong --}}
        <div class="admin-courses-dt-host" aria-hidden="true">
            <table id="courses-table" class="table w-100" data-ajax-url="{{ route('courses.index') }}">
                <thead>
                    <tr>
                        <th>{{ __('Kursus') }}</th>
                        <th>{{ __('Judul') }}</th>
                        <th>{{ __('Kode') }}</th>
                        <th>{{ __('Kategori') }}</th>
                        <th>{{ __('Level') }}</th>
                        <th>{{ __('Modul') }}</th>
                        <th>{{ __('Peserta') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @can('course create')
        <div class="modal fade" id="courseCreateModal" tabindex="-1" aria-labelledby="courseCreateModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="courseCreateModalTitle">{{ __('Tambah kursus') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('courses.store') }}" enctype="multipart/form-data" id="courseCreateForm">
                            @csrf
                            @include('courses.partials.form-fields')
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                        <button type="submit" form="courseCreateForm" class="btn btn-primary btn-wave">{{ __('Simpan') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/admin-courses-grid.css') }}?v={{ @filemtime(public_path('backend/assets/css/admin-courses-grid.css')) ?: time() }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/admin-topics.css') }}?v={{ @filemtime(public_path('backend/assets/css/admin-topics.css')) ?: time() }}">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('backend/assets/js/admin-courses-grid.js') }}?v={{ @filemtime(public_path('backend/assets/js/admin-courses-grid.js')) ?: time() }}"></script>
    <script src="{{ asset('backend/assets/js/admin-course-form.js') }}?v={{ @filemtime(public_path('backend/assets/js/admin-course-form.js')) ?: time() }}"></script>
@endpush
