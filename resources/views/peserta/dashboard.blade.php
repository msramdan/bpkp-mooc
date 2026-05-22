@extends('layouts.app')

@section('title', __('Beranda Peserta'))

@section('content')
    @include('peserta.partials.page-header', ['title' => __('Beranda Peserta')])

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <span class="d-block mb-1 text-muted">{{ __('Kursus aktif') }}</span>
                    <h3 class="fw-semibold mb-0">{{ $stats['kursus_aktif'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <span class="d-block mb-1 text-muted">{{ __('Kursus selesai') }}</span>
                    <h3 class="fw-semibold mb-0">{{ $stats['kursus_selesai'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <span class="d-block mb-1 text-muted">{{ __('Tugas belum dikumpulkan') }}</span>
                    <h3 class="fw-semibold mb-0 text-warning">{{ $stats['tugas_pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card custom-card">
                <div class="card-body">
                    <span class="d-block mb-1 text-muted">{{ __('Ujian mendatang') }}</span>
                    <h3 class="fw-semibold mb-0">{{ $stats['ujian_mendatang'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">{{ __('Kursus saya') }}</div>
                    <a href="{{ route('peserta.kursus.index') }}" class="btn btn-sm btn-primary-light">{{ __('Lihat semua') }}</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($kursus as $enrollment)
                            @php $course = $enrollment->course; @endphp
                            <div class="col-md-4">
                                <a href="{{ route('peserta.kursus.index') }}"
                                    class="d-flex gap-2 text-decoration-none text-body">
                                    <div class="flex-shrink-0 rounded overflow-hidden"
                                        style="width: 120px; aspect-ratio: 16/9;">
                                        <img src="{{ $course->thumbnail }}" alt=""
                                            class="w-100 h-100 object-fit-cover"
                                            onerror="this.src='https://placehold.co/120x68/2b478b/ffffff?text=MOOC'">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="fw-semibold fs-13 mb-1 text-truncate-2"
                                            style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                            {{ $course->judul }}
                                        </p>
                                        <p class="text-muted fs-12 mb-1">{{ $course->instruktur }}</p>
                                        <div class="progress mb-1" style="height: 4px;">
                                            <div class="progress-bar" style="width: {{ $enrollment->progress }}%"></div>
                                        </div>
                                        <span class="fs-11 text-muted">{{ $enrollment->progress }}% ·
                                            @include('peserta.partials.status-badge', ['status' => $enrollment->status])</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title mb-0">{{ __('Jadwal terdekat') }}</div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach ($jadwal as $item)
                            <li class="mb-3 pb-3 border-bottom">
                                <span class="badge bg-light text-dark mb-1">{{ $item['tanggal'] }} · {{ $item['waktu'] }}</span>
                                <p class="mb-0 fw-medium">{{ $item['kegiatan'] }}</p>
                                <small class="text-muted">{{ $item['kursus'] }}</small>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('peserta.jadwal.index') }}" class="btn btn-sm btn-secondary-light w-100">{{ __('Kalender lengkap') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
