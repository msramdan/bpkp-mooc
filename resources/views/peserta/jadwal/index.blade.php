@extends('layouts.app')

@section('title', __('Jadwal Belajar'))

@section('content')
    @include('peserta.partials.page-header', ['title' => __('Jadwal Belajar')])

    <div class="card custom-card">
        <div class="card-body">
            <div class="list-group list-group-flush">
                @foreach ($items as $item)
                    <div class="list-group-item px-0">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <div>
                                <span class="badge bg-primary-transparent mb-2">{{ $item['tanggal'] }} · {{ $item['waktu'] }}</span>
                                <h6 class="mb-1 fw-semibold">{{ $item['kegiatan'] }}</h6>
                                <p class="text-muted fs-13 mb-0">{{ $item['kursus'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
