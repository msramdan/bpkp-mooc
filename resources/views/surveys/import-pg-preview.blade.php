@extends('layouts.app')

@section('title', __('Pratinjau Import Soal PG'))

@php
    $invalidCount = count($rows) - $validCount;
@endphp

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Pratinjau Import Soal PG') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('surveys.index') }}">{{ __('Bank Soal / Kuesioner') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('surveys.builder', $survey) }}">{{ $survey->title }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Pratinjau Import') }}</li>
            </ol>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-4">
            <div class="card custom-card mb-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar avatar-md bg-primary-transparent"><i class="ri-file-list-3-line fs-20"></i></span>
                    <div>
                        <div class="fs-20 fw-semibold lh-1">{{ count($rows) }}</div>
                        <div class="text-muted fs-12">{{ __('Soal terbaca dari file') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card custom-card mb-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar avatar-md bg-success-transparent"><i class="ri-checkbox-circle-line fs-20"></i></span>
                    <div>
                        <div class="fs-20 fw-semibold lh-1 text-success">{{ $validCount }}</div>
                        <div class="text-muted fs-12">{{ __('Siap disimpan') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card custom-card mb-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar avatar-md {{ $invalidCount ? 'bg-danger-transparent' : 'bg-light' }}"><i class="ri-error-warning-line fs-20"></i></span>
                    <div>
                        <div class="fs-20 fw-semibold lh-1 {{ $invalidCount ? 'text-danger' : '' }}">{{ $invalidCount }}</div>
                        <div class="text-muted fs-12">{{ __('Perlu diperbaiki (tidak disimpan)') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="card-title mb-1">{{ __('Daftar Soal') }}</div>
                <div class="text-muted fs-12">
                    <i class="ri-file-excel-2-line me-1"></i>{{ $fileName }}
                    @if ($skippedExamples > 0)
                        · {{ __(':count baris contoh dilewati', ['count' => $skippedExamples]) }}
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($invalidCount > 0)
                <div class="alert alert-warning d-flex gap-2" role="alert">
                    <i class="ri-information-line fs-18"></i>
                    <div>
                        @if ($validCount > 0)
                            {{ __('Ada :invalid soal yang belum lengkap. Anda bisa menyimpan :valid soal yang valid sekarang, atau perbaiki file Excel lalu unggah ulang.', ['invalid' => $invalidCount, 'valid' => $validCount]) }}
                        @else
                            {{ __('Semua soal masih perlu diperbaiki. Perbaiki file Excel sesuai keterangan di bawah, lalu unggah ulang.') }}
                        @endif
                    </div>
                </div>
            @endif

            <div class="d-flex flex-column gap-3">
                @foreach ($rows as $row)
                    @php($isValid = $row['errors'] === [])
                    <div class="import-pg-item {{ $isValid ? '' : 'import-pg-item--invalid' }}">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <div class="d-flex align-items-start gap-2 min-w-0">
                                <span class="badge {{ $isValid ? 'bg-success-transparent' : 'bg-danger-transparent' }} flex-shrink-0">
                                    <i class="{{ $isValid ? 'ri-check-line' : 'ri-close-line' }} me-1"></i>{{ __('Baris :line', ['line' => $row['line']]) }}
                                </span>
                                <div class="fw-semibold text-break">{{ $row['question'] !== '' ? $row['question'] : __('(Pertanyaan kosong)') }}</div>
                            </div>
                            @if ($isValid && ! $row['is_required'])
                                <span class="badge bg-light text-muted flex-shrink-0">{{ __('Opsional') }}</span>
                            @endif
                        </div>

                        @if ($row['options'] !== [])
                            <div class="row g-2">
                                @foreach ($row['options'] as $option)
                                    @php($isKey = $option['letter'] === $row['answer'])
                                    <div class="col-md-6">
                                        <div class="import-pg-option {{ $isKey ? 'import-pg-option--key' : '' }}">
                                            <span class="import-pg-option__letter">{{ $option['letter'] }}</span>
                                            <span class="text-break">{{ $option['text'] }}</span>
                                            @if ($isKey)
                                                <span class="badge bg-success ms-auto flex-shrink-0"><i class="ri-check-line"></i> {{ __('Kunci') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @unless ($isValid)
                            <ul class="import-pg-errors mb-0 mt-2">
                                @foreach ($row['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endunless
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
            <form method="POST" action="{{ route('surveys.import-pg.cancel', $survey) }}">
                @csrf
                <button type="submit" class="btn btn-light btn-wave">
                    <i class="ri-arrow-left-line me-1"></i>{{ __('Batal & Unggah Ulang') }}
                </button>
            </form>
            @if ($validCount > 0)
                <form method="POST" action="{{ route('surveys.import-pg.store', $survey) }}" id="importPgStoreForm">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-wave">
                        <i class="ri-save-3-line me-1"></i>{{ __('Simpan :count Soal', ['count' => $validCount]) }}
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection

@push('css')
    <style>
        .import-pg-item {
            padding: 14px 16px;
            border: 1px solid var(--default-border, #e9ecef);
            border-left: 4px solid rgb(var(--success-rgb, 38, 191, 148));
            border-radius: 8px;
        }

        .import-pg-item--invalid {
            border-left-color: rgb(var(--danger-rgb, 230, 83, 60));
            background-color: rgba(var(--danger-rgb, 230, 83, 60), 0.03);
        }

        .import-pg-option {
            display: flex;
            align-items: center;
            gap: 10px;
            height: 100%;
            padding: 8px 12px;
            border: 1px solid var(--default-border, #e9ecef);
            border-radius: 6px;
            font-size: 13px;
        }

        .import-pg-option--key {
            border-color: rgb(var(--success-rgb, 38, 191, 148));
            background-color: rgba(var(--success-rgb, 38, 191, 148), 0.08);
        }

        .import-pg-option__letter {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: var(--default-border, #e9ecef);
            font-size: 12px;
            font-weight: 600;
        }

        .import-pg-option--key .import-pg-option__letter {
            background-color: rgb(var(--success-rgb, 38, 191, 148));
            color: #ffffff;
        }

        .import-pg-errors {
            padding-left: 18px;
            color: rgb(var(--danger-rgb, 230, 83, 60));
            font-size: 13px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.getElementById('importPgStoreForm')?.addEventListener('submit', function (event) {
            var button = event.target.querySelector('button[type="submit"]');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + @json(__('Menyimpan...'));
        });
    </script>
@endpush
