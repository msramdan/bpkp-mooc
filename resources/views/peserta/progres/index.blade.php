@extends('layouts.app')

@section('title', __('Nilai & Progres'))

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-progres.css" rel="stylesheet">
@endpush

@section('content')
    <div class="peserta-progres-page">
        @include('peserta.partials.page-header', ['title' => __('Nilai & Progres')])

        <div class="peserta-progres-summary">
            <span class="peserta-progres-summary__item peserta-progres-summary__item--accent">
                {{ __('Rata-rata progres') }}
                <strong>{{ $rata_progress }}%</strong>
            </span>
            <span class="peserta-progres-summary__item">
                {{ __('Kursus') }}
                <strong>{{ $enrollments->count() }}</strong>
            </span>
            <span class="peserta-progres-summary__item">
                {{ __('Berlangsung') }}
                <strong>{{ $berlangsung }}</strong>
            </span>
            <span class="peserta-progres-summary__item">
                {{ __('Selesai') }}
                <strong>{{ $selesai }}</strong>
            </span>
        </div>

        <div class="peserta-progres-list">
            @foreach ($enrollments as $enrollment)
                @php $course = $enrollment->course; @endphp
                <article class="peserta-progres-card">
                    <img src="{{ $course->thumbnail }}" alt="" class="peserta-progres-card__thumb"
                        onerror="this.src='https://placehold.co/160x90/2b478b/ffffff?text={{ urlencode($course->kode) }}'">
                    <div class="peserta-progres-card__main">
                        <h3 class="peserta-progres-card__title">{{ $course->judul }}</h3>
                        <p class="peserta-progres-card__meta">{{ $course->kode }} · {{ $course->instruktur }}</p>
                    </div>
                    <div class="peserta-progres-card__progress">
                        <div class="peserta-progres-card__bar">
                            <span style="width: {{ $enrollment->progress }}%"></span>
                        </div>
                        <span class="peserta-progres-card__pct">{{ $enrollment->progress }}%</span>
                    </div>
                    <div class="peserta-progres-card__side">
                        <span class="peserta-progres-card__modul">{{ __('Modul') }} {{ $enrollment->modulLabel() }}</span>
                        @include('peserta.partials.status-badge', ['status' => $enrollment->status])
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endsection
