@extends('layouts.app')

@section('title', __('Sertifikat'))

@section('content')
    @include('peserta.partials.page-header', ['title' => __('Sertifikat')])

    <div class="row">
        @foreach ($items as $item)
            <div class="col-xl-6">
                <div class="card custom-card border">
                    <div class="card-body d-flex gap-3 align-items-start">
                        <span class="avatar avatar-lg bg-success-transparent">
                            <i class="bi bi-award fs-20"></i>
                        </span>
                        <div class="flex-fill">
                            <h5 class="fw-semibold mb-1">{{ $item['kursus'] }}</h5>
                            <p class="text-muted fs-13 mb-2">{{ __('Nomor sertifikat') }}: <code>{{ $item['nomor'] }}</code></p>
                            <div class="d-flex flex-wrap gap-3 fs-13">
                                <span>{{ __('Tanggal terbit') }}: {{ $item['tanggal'] }}</span>
                                <span>{{ __('Nilai akhir') }}: {{ $item['nilai'] }}</span>
                            </div>
                            <div class="mt-2">@include('peserta.partials.status-badge', ['status' => $item['status']])</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary-light" @disabled($item['status'] !== 'Terbit')>
                            {{ __('Unduh PDF') }}
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
