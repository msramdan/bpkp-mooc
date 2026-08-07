@extends('layouts.app')

@section('title', __('Tambah Template Sertifikat'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Tambah Template Sertifikat') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('certificate-templates.index') }}">{{ __('Template Sertifikat') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Tambah Baru') }}</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-10 col-md-12">
            <div class="card custom-card shadow-sm">
                <div class="card-header border-bottom">
                    <div class="card-title mb-0"><i class="bi bi-award-fill text-warning me-2"></i> {{ __('Formulir Desain Template') }}</div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('certificate-templates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Nama / Judul Template') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Contoh: Template Resmi MOOC BPKP 2026" value="{{ old('title') }}" required>
                            <small class="text-muted">Nama penanda internal untuk dipilih instruktur saat meramu modul kursus.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Upload Gambar Latar (A4 Landscape)') }}</label>
                            <input type="file" name="background_file" accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="form-control @error('background_file') is-invalid @enderror">
                            @error('background_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle text-primary me-1"></i>Format PNG/JPG/WEBP. Sangat disarankan rasio <strong>A4 Landscape (1.41 : 1)</strong> dengan resolusi standar cetak <strong>1754 x 1240 px</strong> agar sertifikat tidak buram/pecah saat didownload. Kosongkan untuk memakai gambar bawaan.</small>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-pen-fill me-2"></i> {{ __('Legalitas Penandatangan Sertifikat') }}</h6>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('Nama Lengkap Penandatangan') }}</label>
                                <input type="text" name="signer_name" class="form-control" placeholder="Contoh: Dr. H. Ahmad Ruslan, CFE" value="{{ old('signer_name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('Jabatan Resmi / Instansi') }}</label>
                                <input type="text" name="signer_title" class="form-control" placeholder="Contoh: Kepala Pusdiklatwas BPKP" value="{{ old('signer_title') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Upload Spesimen Tanda Tangan (Opsional)') }}</label>
                            <input type="file" name="signature_file" accept="image/png,image/webp"
                                class="form-control @error('signature_file') is-invalid @enderror">
                            @error('signature_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Format PNG atau WEBP dengan latar belakang transparan. Gambar ini akan diletakkan di atas nama penandatangan saat sertifikat dicetak.</small>
                        </div>

                        <div class="mb-4 form-check form-switch fs-15">
                            <input type="checkbox" name="is_default" value="1" class="form-check-input" id="is_default" {{ old('is_default') ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium cursor-pointer" for="is_default">{{ __('Jadikan sebagai Template Default') }}</label>
                            <div class="fs-12 text-muted">Jika diaktifkan, template ini akan langsung diterapkan secara otomatis bila instruktur mengosongkan pilihan template saat pembuatan materi sertifikat.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('certificate-templates.index') }}" class="btn btn-light px-4">{{ __('Batal') }}</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm"><i class="bi bi-save me-1"></i> {{ __('Simpan Template') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
