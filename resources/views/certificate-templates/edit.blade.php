@extends('layouts.app')

@section('title', __('Edit Template Sertifikat'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Edit Template Sertifikat') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('certificate-templates.index') }}">{{ __('Template Sertifikat') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Edit') }}</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-10 col-md-12">
            <div class="card custom-card shadow-sm">
                <div class="card-header border-bottom">
                    <div class="card-title mb-0"><i class="bi bi-pencil-square text-warning me-2"></i> {{ __('Perbarui Spesifikasi Template') }}</div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('certificate-templates.update', $certificateTemplate) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Nama / Judul Template') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $certificateTemplate->title) }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Upload Gambar Latar (A4 Landscape)') }}</label>
                            @if ($certificateTemplate->background_image_url)
                                <div class="mb-2">
                                    <img src="{{ $certificateTemplate->backgroundUrl() }}" alt="Pratinjau latar" style="max-width:280px;border:1px solid #dee2e6;border-radius:6px;">
                                </div>
                            @endif
                            <input type="file" name="background_file" accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="form-control @error('background_file') is-invalid @enderror">
                            @error('background_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle text-primary me-1"></i>Format PNG/JPG/WEBP. Sangat disarankan rasio <strong>A4 Landscape (1.41 : 1)</strong> dengan resolusi standar cetak <strong>1754 x 1240 px</strong> agar sertifikat tidak buram/pecah saat didownload. Kosongkan jika tidak ingin mengganti gambar latar saat ini.</small>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-pen-fill me-2"></i> {{ __('Legalitas Penandatangan Sertifikat') }}</h6>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('Nama Lengkap Penandatangan') }}</label>
                                <input type="text" name="signer_name" class="form-control" value="{{ old('signer_name', $certificateTemplate->signer_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('Jabatan Resmi / Instansi') }}</label>
                                <input type="text" name="signer_title" class="form-control" value="{{ old('signer_title', $certificateTemplate->signer_title) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Upload Spesimen Tanda Tangan (Opsional)') }}</label>
                            @if ($certificateTemplate->signatureUrl())
                                <div class="mb-2 p-2 bg-light border rounded d-inline-block">
                                    <img src="{{ $certificateTemplate->signatureUrl() }}" alt="Pratinjau tanda tangan" style="max-height: 80px; display: block;">
                                    <small class="text-muted d-block mt-1 text-center">Spesimen saat ini</small>
                                </div>
                            @endif
                            <input type="file" name="signature_file" accept="image/png,image/webp"
                                class="form-control @error('signature_file') is-invalid @enderror">
                            @error('signature_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Format PNG atau WEBP dengan latar belakang transparan. Kosongkan jika tidak ingin mengganti spesimen yang sudah ada.</small>
                        </div>

                        <div class="mb-4 form-check form-switch fs-15">
                            <input type="checkbox" name="is_default" value="1" class="form-check-input" id="is_default" {{ old('is_default', $certificateTemplate->is_default) ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium cursor-pointer" for="is_default">{{ __('Jadikan sebagai Template Default') }}</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('certificate-templates.index') }}" class="btn btn-light px-4">{{ __('Batal') }}</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm"><i class="bi bi-check-lg me-1"></i> {{ __('Perbarui Template') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
