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

        <div class="admin-course-panel">
            <div class="admin-course-toolbar">
                <div id="courses-length"></div>
                <div id="courses-search"></div>
            </div>
            <p class="admin-course-toolbar__info" id="courses-info"></p>
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

        <div class="admin-course-pagination" id="courses-pagination"></div>

        <div class="visually-hidden">
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
        <div class="modal fade" id="courseCreateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <form method="POST" action="{{ route('courses.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Tambah kursus') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @include('courses.partials.form-fields')
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                            <button type="submit" class="btn btn-primary btn-wave">{{ __('Simpan') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/admin-courses-grid.css') }}">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('backend/assets/js/admin-courses-grid.js') }}"></script>
@endpush
