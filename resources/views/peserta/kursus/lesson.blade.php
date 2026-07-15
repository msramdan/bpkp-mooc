@extends('layouts.app')

@section('title', $lesson->judul)

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-kursus-detail.css" rel="stylesheet">
    <style>
        .peserta-lesson-player__frame { width: 100%; height: 100%; min-height: 420px; border: 0; border-radius: .5rem; background: #000; object-fit: contain; }
        .peserta-lesson-player__body { line-height: 1.7; }
        .peserta-lesson-player__body img { max-width: 100%; height: auto; }
    </style>
@endpush

@section('content')
    @php
        $type = $lesson->normalizedType();
    @endphp

    @include('peserta.partials.page-header', [
        'title' => $lesson->judul,
        'parent' => \Illuminate\Support\Str::limit($course->judul, 40),
        'parentUrl' => route('peserta.kursus.show', $course),
    ])

    <x-alert />

    <div class="card custom-card mb-3">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="badge bg-primary-transparent me-1">{{ $lesson->typeLabel() }}</span>
                <span class="text-muted fs-12">{{ __('Topik') }} {{ $lesson->module->urutan }}</span>
            </div>
            <a href="{{ route('peserta.kursus.show', $course) }}" class="btn btn-sm btn-light btn-wave">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Kembali ke kursus') }}
            </a>
        </div>
        <div class="card-body">
            @if ($lesson->sanitizedBody())
                <div class="peserta-lesson-player__body mb-3">
                    {!! $lesson->sanitizedBody() !!}
                </div>
            @endif

            @if ($type === 'video' && $lesson->isStreamableVideo())
                <div class="ratio ratio-16x9 mb-3">
                    <video class="peserta-lesson-player__frame" controls preload="metadata"
                        src="{{ $lesson->resolveMediaUrlPublic() }}" title="{{ $lesson->judul }}">
                        {{ __('Browser Anda tidak mendukung pemutar video.') }}
                    </video>
                </div>
            @elseif ($type === 'video' && $lesson->embedVideoUrl())
                <div class="ratio ratio-16x9 mb-3">
                    <iframe class="peserta-lesson-player__frame" src="{{ $lesson->embedVideoUrl() }}"
                        title="{{ $lesson->judul }}" allowfullscreen loading="lazy"></iframe>
                </div>
            @elseif ($type === 'berkas' && $lesson->externalUrl())
                <div class="mb-3">
                    <iframe class="peserta-lesson-player__frame" src="{{ $lesson->externalUrl() }}" title="{{ $lesson->judul }}"></iframe>
                </div>
                <p class="mb-0">
                    <a href="{{ $lesson->externalUrl() }}" class="btn btn-sm btn-outline-primary btn-wave" target="_blank" rel="noopener">
                        <i class="bi bi-download me-1"></i>{{ __('Buka / unduh berkas') }}
                    </a>
                </p>
            @elseif ($type === 'url' && $lesson->externalUrl())
                <p class="mb-3">
                    <a href="{{ $lesson->externalUrl() }}" class="btn btn-primary btn-wave" target="_blank" rel="noopener">
                        <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('Buka tautan') }}
                    </a>
                </p>
            @elseif ($type === 'video' || $type === 'berkas' || $type === 'url')
                <p class="text-muted mb-3">{{ __('Konten aktivitas belum tersedia.') }}</p>
            @elseif ($lesson->externalUrl() || $lesson->resolveMediaUrlPublic())
                <p class="mb-3">
                    <a href="{{ $lesson->externalUrl() ?: $lesson->resolveMediaUrlPublic() }}" class="btn btn-primary btn-wave" target="_blank" rel="noopener">
                        <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('Buka materi') }}
                    </a>
                </p>
            @endif

            @if (in_array($type, ['pre_test', 'post_test', 'h5p', 'scorm', 'penugasan', 'forum', 'survey', 'sertifikat'], true))
                <div class="alert alert-info mb-0">
                    {{ __('Tipe aktivitas ini sedang disiapkan. Anda tetap dapat menandai selesai setelah mempelajari konten yang tersedia.') }}
                </div>
            @endif
        </div>
        <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex gap-2">
                @if ($previousLesson)
                    <a href="{{ route('peserta.kursus.lessons.show', [$course, $previousLesson]) }}"
                        class="btn btn-sm btn-light btn-wave">
                        <i class="bi bi-chevron-left"></i> {{ __('Sebelumnya') }}
                    </a>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if (! $isCompleted)
                    <form action="{{ route('peserta.kursus.lessons.complete', [$course, $lesson]) }}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary btn-wave">
                            <i class="bi bi-check-lg me-1"></i>{{ __('Tandai selesai') }}
                        </button>
                    </form>
                @else
                    <span class="badge bg-success-transparent align-self-center">
                        <i class="bi bi-check-circle me-1"></i>{{ __('Selesai') }}
                    </span>
                @endif
                @if ($nextLesson && ($isCompleted || $lesson->is_preview))
                    <a href="{{ route('peserta.kursus.lessons.show', [$course, $nextLesson]) }}"
                        class="btn btn-sm btn-outline-primary btn-wave {{ $nextAccessible || $isCompleted ? '' : 'disabled' }}"
                        @if (! $nextAccessible && ! $isCompleted) aria-disabled="true" @endif>
                        {{ __('Selanjutnya') }} <i class="bi bi-chevron-right"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
