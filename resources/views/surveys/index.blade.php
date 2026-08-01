@extends('layouts.app')

@section('title', __('Bank Soal / Kuesioner'))

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Bank Soal / Kuesioner') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Bank Soal / Kuesioner') }}</li>
            </ol>
        </div>
        <a href="{{ route('surveys.create') }}" class="btn btn-primary btn-wave">
            <i class="ri-add-line align-middle me-1"></i>{{ __('Tambah Soal / Kuesioner') }}
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

            @php
                // VARIABEL PENGATURAN BATAS HURUF / KARAKTER               
                $charLimit = 40;
            @endphp

            <div class="table-responsive">
                <table class="table text-nowrap table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Judul') }}</th>
                            <th scope="col">{{ __('Deskripsi') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                            <th scope="col">{{ __('Pertanyaan') }}</th>
                            <th scope="col">{{ __('Estimasi Waktu') }}</th>
                            <th scope="col" class="text-center">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($surveys as $survey)
                            <tr>
                                <td class="text-wrap" style="max-width: 300px;">
                                    @if(Str::length($survey->title) > $charLimit)
                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $survey->title }}" style="cursor: pointer; border-bottom: 1px dotted #888;">
                                            {{ Str::limit($survey->title, $charLimit, '...') }}
                                        </span>
                                    @else
                                        {{ $survey->title }}
                                    @endif
                                </td>
                                <td class="text-wrap" style="max-width: 380px;">
                                    @if(Str::length($survey->description) > $charLimit)
                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $survey->description }}" style="cursor: pointer; border-bottom: 1px dotted #888;">
                                            {{ Str::limit($survey->description, $charLimit, '...') }}
                                        </span>
                                    @else
                                        {{ $survey->description }}
                                    @endif
                                </td>
                                <td>
                                    @if($survey->is_active)
                                        <span class="badge bg-success-transparent">{{ __('Aktif') }}</span>
                                    @else
                                        <span class="badge bg-danger-transparent">{{ __('Tidak Aktif') }}</span>
                                    @endif
                                </td>
                                <td>{{ $survey->questions()->count() }} {{ __('Butir') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-clock me-1"></i>~{{ max(5, $survey->questions()->count() * 2) }} {{ __('Menit') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-list">
                                        <a href="{{ route('surveys.builder', $survey) }}" class="btn btn-sm btn-primary-light btn-wave" title="Pembangun Kuesioner (Builder)">
                                            <i class="ri-list-settings-line"></i> Builder
                                        </a>
                                        <a href="{{ route('surveys.recap.index', $survey) }}" class="btn btn-sm btn-success-light btn-wave" title="Rekap Nilai & Analitik">
                                            <i class="ri-bar-chart-2-line"></i> Rekap
                                        </a>
                                        <a href="{{ route('surveys.edit', $survey) }}" class="btn btn-sm btn-info-light btn-wave" title="Edit Info">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('surveys.destroy', $survey) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger-light btn-wave js-delete-confirm" 
                                                data-swal-title="{{ __('Hapus Kuesioner ini?') }}" 
                                                data-swal-text="{{ __(' Seluruh pertanyaan dan riwayat jawaban akan terhapus.') }}"
                                                data-swal-confirm="{{ __('Ya, Hapus') }}" 
                                                data-swal-cancel="{{ __('Batal') }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('Belum ada survey yang dibuat.') }}</td>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
