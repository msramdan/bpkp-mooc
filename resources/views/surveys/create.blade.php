@extends('layouts.app')

@section('title', __('Tambah Bank Survey'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Tambah Bank Survey') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('surveys.index') }}">{{ __('Bank Survey') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Tambah') }}</li>
            </ol>
        </div>
        <a href="{{ route('surveys.index') }}" class="btn btn-light btn-wave">
            <i class="ri-arrow-left-line align-middle me-1"></i>{{ __('Kembali') }}
        </a>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">{{ __('Informasi Utama Survey') }}</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('surveys.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ __('Judul Survey') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Deskripsi (Opsional)') }}</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">{{ __('Aktifkan survey ini agar dapat digunakan di kursus') }}</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-wave">
                            <i class="ri-save-line me-1"></i>{{ __('Simpan Survey') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
