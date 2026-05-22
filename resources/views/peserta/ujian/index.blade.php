@extends('layouts.app')

@section('title', __('Ujian & Kuis'))

@section('content')
    @include('peserta.partials.page-header', ['title' => __('Ujian & Kuis')])

    <div class="row">
        @foreach ($items as $item)
            <div class="col-xl-4 col-md-6">
                <div class="card custom-card h-100">
                    <div class="card-body">
                        <p class="text-muted fs-12 mb-1">{{ $item['kursus'] }}</p>
                        <h5 class="fw-semibold mb-2">{{ $item['judul'] }}</h5>
                        <ul class="list-unstyled fs-13 mb-3">
                            <li><i class="bi bi-clock me-1"></i> {{ $item['durasi'] }}</li>
                            <li><i class="bi bi-list-check me-1"></i> {{ $item['jumlah_soal'] }} {{ __('soal') }}</li>
                            <li><i class="bi bi-calendar-event me-1"></i> {{ $item['jadwal'] }}</li>
                        </ul>
                        <div class="d-flex justify-content-between align-items-center">
                            @include('peserta.partials.status-badge', ['status' => $item['status']])
                            <button type="button" class="btn btn-sm btn-primary-light" disabled>{{ __('Mulai') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
