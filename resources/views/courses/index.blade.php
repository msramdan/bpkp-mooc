@extends('layouts.app')

@section('title', __('Kursus'))

@section('content')
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

    <div class="card custom-card">
        <div class="card-body">
            <x-alert />
            <div class="table-responsive">
                <table id="courses-table" class="table table-striped table-bordered text-nowrap w-100">
                    <thead>
                        <tr>
                            <th>{{ __('Kode') }}</th>
                            <th>{{ __('Judul') }}</th>
                            <th>{{ __('Kategori') }}</th>
                            <th>{{ __('Level') }}</th>
                            <th>{{ __('Modul') }}</th>
                            <th>{{ __('Peserta') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
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
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#courses-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: @json(route('courses.index')),
                pageLength: 10,
                order: [[0, 'asc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                columns: [
                    { data: 'kode', name: 'kode' },
                    { data: 'judul', name: 'judul' },
                    { data: 'kategori', name: 'kategori' },
                    { data: 'level', name: 'level' },
                    { data: 'modul_total', name: 'modul_total' },
                    { data: 'enrollments_count', name: 'enrollments_count', searchable: false },
                    { data: 'published_label', name: 'is_published', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                ],
            });
        });
    </script>
@endpush
