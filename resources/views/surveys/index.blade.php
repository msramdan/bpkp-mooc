@extends('layouts.app')

@section('title', __('Bank Survey'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Bank Survey') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Bank Survey') }}</li>
            </ol>
        </div>
        <a href="{{ route('surveys.create') }}" class="btn btn-primary btn-wave">
            <i class="ri-add-line align-middle me-1"></i>{{ __('Tambah Survey') }}
        </a>
    </div>

    <div class="card custom-card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table text-nowrap table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Judul') }}</th>
                            <th scope="col">{{ __('Deskripsi') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                            <th scope="col">{{ __('Pertanyaan') }}</th>
                            <th scope="col" class="text-center">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($surveys as $survey)
                            <tr>
                                <td>{{ $survey->title }}</td>
                                <td>{{ Str::limit($survey->description, 50) }}</td>
                                <td>
                                    @if($survey->is_active)
                                        <span class="badge bg-success-transparent">{{ __('Aktif') }}</span>
                                    @else
                                        <span class="badge bg-danger-transparent">{{ __('Tidak Aktif') }}</span>
                                    @endif
                                </td>
                                <td>{{ $survey->questions()->count() }} {{ __('Butir') }}</td>
                                <td class="text-center">
                                    <div class="btn-list">
                                        <a href="{{ route('surveys.builder', $survey) }}" class="btn btn-sm btn-primary-light btn-wave" title="Pembangun Kuesioner (Builder)">
                                            <i class="ri-list-settings-line"></i> Builder
                                        </a>
                                        <a href="{{ route('surveys.edit', $survey) }}" class="btn btn-sm btn-info-light btn-wave" title="Edit Info">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('surveys.destroy', $survey) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger-light btn-wave" onclick="return confirm('Apakah Anda yakin ingin menghapus survey ini?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ __('Belum ada survey yang dibuat.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $surveys->links() }}
            </div>
        </div>
    </div>
@endsection
