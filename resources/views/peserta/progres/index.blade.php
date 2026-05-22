@extends('layouts.app')

@section('title', __('Nilai & Progres'))

@section('content')
    @include('peserta.partials.page-header', ['title' => __('Nilai & Progres')])

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <span class="text-muted d-block mb-1">{{ __('Rata-rata progres') }}</span>
                    <h2 class="fw-semibold mb-0 text-primary">{{ $rata_progress }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 100px;">{{ __('Kursus') }}</th>
                            <th>{{ __('Detail') }}</th>
                            <th>{{ __('Modul') }}</th>
                            <th>{{ __('Progres') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($enrollments as $enrollment)
                            @php $course = $enrollment->course; @endphp
                            <tr>
                                <td>
                                    <img src="{{ $course->thumbnail }}" alt=""
                                        class="rounded"
                                        style="width: 88px; aspect-ratio: 16/9; object-fit: cover;"
                                        onerror="this.src='https://placehold.co/88x50/2b478b/ffffff?text=MOOC'">
                                </td>
                                <td>
                                    <span class="fw-medium d-block">{{ $course->judul }}</span>
                                    <small class="text-muted">{{ $course->kode }} · {{ $course->instruktur }}</small>
                                </td>
                                <td>{{ $enrollment->modulLabel() }}</td>
                                <td style="min-width: 160px;">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" style="width: {{ $enrollment->progress }}%"></div>
                                    </div>
                                    <small>{{ $enrollment->progress }}%</small>
                                </td>
                                <td>@include('peserta.partials.status-badge', ['status' => $enrollment->status])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
