@extends('layouts.app')

@section('title', $lesson->judul)

@push('css')
    <link href="{{ asset('backend') }}/assets/css/peserta-kursus-detail.css" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/peserta-lesson.css') }}?v={{ @filemtime(public_path('backend/assets/css/peserta-lesson.css')) ?: time() }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $type = $lesson->normalizedType();
        $accent = $typeMeta['color'] ?? '#2b488b';
        $icon = $typeMeta['icon'] ?? 'bi-circle';
        $penugasanMaxMb = round(((int) config('mooc.penugasan_max_kb', 10240)) / 1024, 1);
        $penugasanMimes = implode(', ', (array) config('mooc.penugasan_mimes', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip']));
    @endphp

    @include('peserta.partials.page-header', [
        'title' => $lesson->judul,
        'parent' => \Illuminate\Support\Str::limit($course->judul, 40),
        'parentUrl' => route('peserta.kursus.show', $course),
    ])

    <x-alert />

    <div class="peserta-lesson" style="--lesson-accent: {{ $accent }};">
        <header class="peserta-lesson__header">
            <div class="peserta-lesson__type">
                <span class="peserta-lesson__type-icon" aria-hidden="true"><i class="{{ $icon }}"></i></span>
                <div class="min-w-0">
                    <p class="peserta-lesson__type-label mb-0">{{ $lesson->typeLabel() }}</p>
                    <p class="peserta-lesson__topic mb-0">{{ __('Topik') }} {{ $lesson->module->urutan }} · {{ $lesson->module->judul }}</p>
                </div>
            </div>
            <div class="peserta-lesson__header-actions">
                @if ($isCompleted)
                    <span class="peserta-lesson__status is-done">
                        <i class="bi bi-check-circle-fill"></i>{{ __('Selesai') }}
                    </span>
                @else
                    <span class="peserta-lesson__status">
                        <i class="bi bi-hourglass-split"></i>{{ __('Belum selesai') }}
                    </span>
                @endif
                <a href="{{ route('peserta.kursus.show', $course) }}" class="btn btn-sm btn-light btn-wave">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('Kembali') }}
                </a>
            </div>
        </header>

        <div class="peserta-lesson__grid">
            <section class="peserta-lesson__stage">
                @if ($lesson->sanitizedBody())
                    <article class="peserta-lesson__card peserta-lesson__card--brief">
                        <h2 class="peserta-lesson__card-title">{{ __('Instruksi') }}</h2>
                        <div class="peserta-lesson__body">
                            {!! $lesson->sanitizedBody() !!}
                        </div>
                    </article>
                @endif

                @if ($type === 'video' && $lesson->isStreamableVideo())
                    <article class="peserta-lesson__card peserta-lesson__card--media">
                        <div class="peserta-lesson__media">
                            <video class="peserta-lesson__player" controls preload="metadata"
                                src="{{ $lesson->resolveMediaUrlPublic() }}" title="{{ $lesson->judul }}">
                                {{ __('Browser Anda tidak mendukung pemutar video.') }}
                            </video>
                        </div>
                    </article>
                @elseif ($type === 'video' && $lesson->embedVideoUrl())
                    <article class="peserta-lesson__card peserta-lesson__card--media">
                        <div class="peserta-lesson__media ratio ratio-16x9">
                            <iframe class="peserta-lesson__player" src="{{ $lesson->embedVideoUrl() }}"
                                title="{{ $lesson->judul }}" allowfullscreen loading="lazy"></iframe>
                        </div>
                    </article>
                @elseif ($type === 'berkas' && $lesson->externalUrl())
                    <article class="peserta-lesson__card peserta-lesson__card--media">
                        <div class="peserta-lesson__file-hero">
                            <div class="peserta-lesson__file-glow" aria-hidden="true"></div>
                            <i class="bi bi-file-earmark-richtext peserta-lesson__file-icon"></i>
                            <h3 class="peserta-lesson__file-title">{{ __('Berkas materi siap dibuka') }}</h3>
                            <p class="peserta-lesson__file-text">{{ __('Unduh atau buka berkas di tab baru untuk mempelajari materi.') }}</p>
                            <a href="{{ $lesson->externalUrl() }}" class="btn btn-primary btn-wave" target="_blank" rel="noopener">
                                <i class="bi bi-download me-1"></i>{{ __('Buka / unduh berkas') }}
                            </a>
                        </div>
                        <div class="peserta-lesson__preview mt-3">
                            <iframe src="{{ $lesson->externalUrl() }}" title="{{ $lesson->judul }}" loading="lazy"></iframe>
                        </div>
                    </article>
                @elseif ($type === 'url' && $lesson->externalUrl())
                    <article class="peserta-lesson__card">
                        <div class="peserta-lesson__link-hero">
                            <i class="bi bi-link-45deg"></i>
                            <h3>{{ __('Tautan eksternal') }}</h3>
                            <p>{{ __('Aktivitas ini mengarah ke halaman di luar aplikasi.') }}</p>
                            <a href="{{ $lesson->externalUrl() }}" class="btn btn-primary btn-wave" target="_blank" rel="noopener">
                                <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('Buka tautan') }}
                            </a>
                        </div>
                    </article>
                @elseif ($type === 'penugasan')
                    <article class="peserta-lesson__card">
                        @if ($lesson->externalUrl())
                            <div class="peserta-lesson__assign-instruction">
                                <div class="peserta-lesson__assign-badge">
                                    <i class="bi bi-journal-text"></i>{{ __('Materi / instruksi') }}
                                </div>
                                <p class="mb-3">{{ __('Pelajari berkas instruksi berikut, lalu kumpulkan hasil pengerjaan Anda.') }}</p>
                                <a href="{{ $lesson->externalUrl() }}" class="btn btn-outline-primary btn-wave" target="_blank" rel="noopener">
                                    <i class="bi bi-download me-1"></i>{{ __('Unduh berkas instruksi') }}
                                </a>
                            </div>
                        @endif

                        <div class="peserta-lesson__assign-panel">
                            <div class="peserta-lesson__assign-head">
                                <h3 class="mb-0">{{ __('Kumpulkan hasil pengerjaan') }}</h3>
                                <p class="mb-0 text-muted fs-13">
                                    {{ __('Format: :formats — maks. :max MB', ['formats' => strtoupper($penugasanMimes), 'max' => $penugasanMaxMb]) }}
                                </p>
                            </div>

                            @if ($submission)
                                <div class="peserta-lesson__submission is-done">
                                    <div class="peserta-lesson__submission-icon"><i class="bi bi-check2-circle"></i></div>
                                    <div class="min-w-0">
                                        <div class="peserta-lesson__submission-name text-truncate">{{ $submission->original_name }}</div>
                                        <div class="peserta-lesson__submission-meta">
                                            {{ $submission->humanSize() }}
                                            · {{ $submission->submitted_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}
                                        </div>
                                    </div>
                                    @if ($submission->publicUrl())
                                        <a href="{{ $submission->publicUrl() }}" class="btn btn-sm btn-light btn-wave" target="_blank" rel="noopener">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                </div>
                            @endif

                            <form method="POST" action="{{ route('peserta.kursus.lessons.submit', [$course, $lesson]) }}"
                                enctype="multipart/form-data" class="peserta-lesson__upload">
                                @csrf
                                <label class="peserta-lesson__dropzone" for="submission_file">
                                    <input type="file" name="submission_file" id="submission_file" class="visually-hidden js-upload-size"
                                        accept=".pdf,.doc,.docx,.ppt,.pptx,.zip"
                                        data-max-kb="{{ (int) config('mooc.penugasan_max_kb', 10240) }}"
                                        data-label="{{ __('Hasil pengerjaan') }}"
                                        required>
                                    <span class="peserta-lesson__dropzone-icon"><i class="bi bi-cloud-arrow-up"></i></span>
                                    <span class="peserta-lesson__dropzone-title">
                                        {{ $submission ? __('Ganti berkas hasil pengerjaan') : __('Seret berkas ke sini atau klik untuk memilih') }}
                                    </span>
                                    <span class="peserta-lesson__dropzone-file text-muted fs-12" data-file-name></span>
                                </label>
                                @error('submission_file')
                                    <div class="text-danger fs-13 mt-2">{{ $message }}</div>
                                @enderror
                                <button type="submit" class="btn btn-primary btn-wave w-100 mt-3">
                                    <i class="bi bi-send-check me-1"></i>
                                    {{ $submission ? __('Perbarui pengumpulan') : __('Kumpulkan sekarang') }}
                                </button>
                            </form>
                        </div>
                    </article>
                @elseif ($type === 'video' || $type === 'berkas' || $type === 'url')
                    <article class="peserta-lesson__card">
                        <p class="text-muted mb-0">{{ __('Konten aktivitas belum tersedia.') }}</p>
                    </article>
                @elseif ($type === 'survey')
                    <article class="peserta-lesson__card">
                        <div class="peserta-lesson__soon">
                            <i class="bi bi-clipboard2-data"></i>
                            <p class="mb-0">{{ __('Belum ada pertanyaan pada survey ini.') }}</p>
                        </div>
                    </article>
                @elseif ($type === 'h5p')
                    <article class="peserta-lesson__card" style="padding: 0; overflow: hidden; border: none; background: transparent;">
                        <div id="h5p-container"></div>
                    </article>
                @elseif (in_array($type, ['pre_test', 'post_test', 'scorm', 'forum', 'sertifikat'], true))
                    <article class="peserta-lesson__card">
                        <div class="peserta-lesson__soon">
                            <i class="bi bi-stars"></i>
                            <p class="mb-0">{{ __('Tipe aktivitas ini sedang disiapkan. Anda tetap dapat menandai selesai setelah mempelajari konten yang tersedia.') }}</p>
                        </div>
                    </article>
                @elseif ($lesson->externalUrl() || $lesson->resolveMediaUrlPublic())
                    <article class="peserta-lesson__card">
                        <a href="{{ $lesson->externalUrl() ?: $lesson->resolveMediaUrlPublic() }}" class="btn btn-primary btn-wave" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('Buka materi') }}
                        </a>
                    </article>
                @endif
            </section>

            <aside class="peserta-lesson__aside">
                <div class="peserta-lesson__card peserta-lesson__card--aside">
                    <h2 class="peserta-lesson__card-title">{{ __('Progres aktivitas') }}</h2>
                    <ul class="peserta-lesson__meta-list">
                        <li>
                            <span>{{ __('Tipe') }}</span>
                            <strong>{{ $lesson->typeLabel() }}</strong>
                        </li>
                        <li>
                            <span>{{ __('Status') }}</span>
                            <strong>{{ $isCompleted ? __('Selesai') : __('Belum selesai') }}</strong>
                        </li>
                        @if ($type === 'penugasan')
                            <li>
                                <span>{{ __('Pengumpulan') }}</span>
                                <strong>{{ $submission ? __('Sudah dikumpulkan') : __('Belum dikumpulkan') }}</strong>
                            </li>
                        @endif
                    </ul>

                    <div class="peserta-lesson__nav-btns">
                        @if ($previousLesson)
                            <a href="{{ route('peserta.kursus.lessons.show', [$course, $previousLesson]) }}"
                                class="btn btn-light btn-wave">
                                <i class="bi bi-chevron-left"></i> {{ __('Sebelumnya') }}
                            </a>
                        @endif

                        @if (! $isCompleted && $type !== 'penugasan')
                            <form action="{{ route('peserta.kursus.lessons.complete', [$course, $lesson]) }}" method="post" class="flex-grow-1">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-wave w-100">
                                    <i class="bi bi-check-lg me-1"></i>{{ __('Tandai selesai') }}
                                </button>
                            </form>
                        @elseif (! $isCompleted && $type === 'penugasan' && $submission)
                            <form action="{{ route('peserta.kursus.lessons.complete', [$course, $lesson]) }}" method="post" class="flex-grow-1">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-wave w-100">
                                    <i class="bi bi-check-lg me-1"></i>{{ __('Tandai selesai') }}
                                </button>
                            </form>
                        @endif

                        @if ($nextLesson && ($isCompleted || $lesson->is_preview || ($type === 'penugasan' && $submission)))
                            <a href="{{ route('peserta.kursus.lessons.show', [$course, $nextLesson]) }}"
                                class="btn btn-outline-primary btn-wave {{ $nextAccessible || $isCompleted || ($type === 'penugasan' && $submission) ? '' : 'disabled' }}"
                                @if (! $nextAccessible && ! $isCompleted && ! ($type === 'penugasan' && $submission)) aria-disabled="true" @endif>
                                {{ __('Selanjutnya') }} <i class="bi bi-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('styles')
    @if ($type === 'h5p')
        <link rel="stylesheet" href="{{ asset('vendor/h5p-standalone/styles/h5p.css') }}">
        <style>
            #h5p-container {
                max-width: 100%;
                margin: 0 auto;
            }
        </style>
    @endif
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('submission_file');
            var nameEl = document.querySelector('[data-file-name]');
            if (!input || !nameEl) return;
            input.addEventListener('change', function () {
                nameEl.textContent = input.files && input.files[0] ? input.files[0].name : '';
            });
            document.querySelectorAll('.js-upload-size').forEach(function (el) {
                el.addEventListener('change', function () {
                    var maxKb = parseInt(el.getAttribute('data-max-kb') || '0', 10);
                    if (!maxKb || !el.files || !el.files.length) return;
                    if (el.files[0].size <= maxKb * 1024) return;
                    var maxMb = Math.round((maxKb / 1024) * 10) / 10;
                    alert((el.getAttribute('data-label') || 'File') + ' maksimal ' + maxMb + ' MB.');
                    el.value = '';
                    nameEl.textContent = '';
                });
            });
        });
    </script>
    @if ($type === 'h5p')
        <script src="{{ asset('vendor/h5p-standalone/main.bundle.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', async function() {
                const el = document.getElementById('h5p-container');
                if (!el) return;

                const options = {
                    h5pJsonPath: '{{ route('h5p.assets', ['lesson' => $lesson, 'path' => '']) }}',
                    frameJs: '{{ asset('vendor/h5p-standalone/frame.bundle.js') }}',
                    frameCss: '{{ asset('vendor/h5p-standalone/styles/h5p.css') }}',
                };
                
                try {
                    await new H5PStandalone.H5P(el, options);
                } catch (err) {
                    console.error('Error initializing H5P:', err);
                    el.innerHTML = '<div class="alert alert-danger">Gagal memuat player H5P.</div>';
                }
            });
        </script>
    @endif
@endpush
