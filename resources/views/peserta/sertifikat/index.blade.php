@extends('layouts.app')

@section('title', __('Sertifikat'))

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-sertifikat.css" rel="stylesheet">
@endpush

@section('content')
    @include('peserta.partials.page-header', ['title' => __('Sertifikat')])

    <div class="peserta-sertifikat-wrap">
    <div class="peserta-sertifikat-stats">
        <div class="peserta-sertifikat-stat peserta-sertifikat-stat--issued">
            <span class="peserta-sertifikat-stat__icon"><i class="bi bi-patch-check-fill"></i></span>
            <div>
                <span class="text-muted fs-12 d-block">{{ __('Sertifikat terbit') }}</span>
                <h4 class="fw-semibold mb-0">{{ $stats['terbit'] }}</h4>
            </div>
        </div>
        <div class="peserta-sertifikat-stat peserta-sertifikat-stat--pending">
            <span class="peserta-sertifikat-stat__icon"><i class="bi bi-clock-history"></i></span>
            <div>
                <span class="text-muted fs-12 d-block">{{ __('Menunggu kelulusan') }}</span>
                <h4 class="fw-semibold mb-0">{{ $stats['menunggu'] }}</h4>
            </div>
        </div>
    </div>

    @if ($items->isEmpty())
        <div class="peserta-sertifikat-empty">
            <i class="bi bi-award fs-1 d-block mb-3 opacity-50"></i>
            <h5 class="fw-semibold">{{ __('Belum ada sertifikat') }}</h5>
            <p class="mb-0 fs-13">{{ __('Selesaikan semua materi kursus untuk mendapatkan sertifikat.') }}</p>
        </div>
    @else
        <div class="peserta-sertifikat-grid">
            @foreach ($items as $item)
                @include('peserta.sertifikat._card', ['item' => $item])
            @endforeach
        </div>
    @endif
    </div>
@endsection
