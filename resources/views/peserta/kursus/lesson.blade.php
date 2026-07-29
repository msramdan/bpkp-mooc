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
                                <p class="mb-3">{{ __('Pelajari instruksi berikut, lalu kirim jawaban sesuai kebutuhan penugasan.') }}</p>
                                <a href="{{ $lesson->externalUrl() }}" class="btn btn-outline-primary btn-wave" target="_blank" rel="noopener">
                                    <i class="bi bi-download me-1"></i>{{ __('Unduh berkas instruksi') }}
                                </a>
                            </div>
                        @endif

                        <div class="peserta-lesson__assign-panel">
                            <div class="peserta-lesson__assign-head">
                                <h3 class="mb-0">{{ __('Kumpulkan hasil pengerjaan') }}</h3>
                                <p class="mb-0 text-muted fs-13">{{ __('Lengkapi komponen jawaban yang diwajibkan oleh pengajar.') }}</p>
                            </div>

                            @if ($submission)
                                <div class="peserta-lesson__submission is-done">
                                    <div class="peserta-lesson__submission-icon"><i class="bi bi-check2-circle"></i></div>
                                    <div class="min-w-0">
                                        <div class="peserta-lesson__submission-name text-truncate">
                                            {{ $submission->original_name ?: __('Jawaban telah tersimpan') }}
                                        </div>
                                        <div class="peserta-lesson__submission-meta">
                                            @if ($submission->file_path)
                                                {{ $submission->humanSize() }} ·
                                            @endif
                                            {{ $submission->submitted_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}
                                        </div>
                                    </div>
                                    @if ($submission->publicUrl())
                                        <a href="{{ $submission->publicUrl() }}" class="btn btn-sm btn-light btn-wave" target="_blank" rel="noopener">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                </div>
                                @if ($submission->submission_text)
                                    <div class="mt-3">
                                        <label class="form-label mb-1">{{ __('Uraian jawaban') }}</label>
                                        <div class="form-control bg-light" style="min-height: 90px;">{{ $submission->submission_text }}</div>
                                    </div>
                                @endif
                                @if ($submission->submission_link)
                                    <div class="mt-3">
                                        <label class="form-label mb-1">{{ __('Tautan jawaban') }}</label>
                                        <div>
                                            <a href="{{ $submission->submission_link }}" target="_blank" rel="noopener">{{ $submission->submission_link }}</a>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <form method="POST" action="{{ route('peserta.kursus.lessons.submit', [$course, $lesson]) }}"
                                enctype="multipart/form-data" class="peserta-lesson__upload">
                                @csrf
                                @if ($lesson->assignment_allow_text)
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Uraian jawaban') }}</label>
                                        <textarea name="submission_text" rows="4" class="form-control"
                                            placeholder="{{ __('Tulis jawaban atau penjelasan Anda di sini...') }}">{{ old('submission_text', $submission?->submission_text) }}</textarea>
                                        @error('submission_text')
                                            <div class="text-danger fs-13 mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                @if ($lesson->assignment_allow_link)
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Tautan jawaban') }}</label>
                                        <input type="url" name="submission_link" class="form-control"
                                            value="{{ old('submission_link', $submission?->submission_link) }}"
                                            placeholder="https://contoh.com/jawaban">
                                        @error('submission_link')
                                            <div class="text-danger fs-13 mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                @if ($lesson->assignment_allow_file)
                                    <label class="peserta-lesson__dropzone" for="submission_file">
                                        <input type="file" name="submission_file" id="submission_file" class="visually-hidden js-upload-size"
                                            accept=".pdf,.doc,.docx,.ppt,.pptx,.zip"
                                            data-max-kb="{{ (int) config('mooc.penugasan_max_kb', 10240) }}"
                                            data-label="{{ __('Hasil pengerjaan') }}">
                                        <span class="peserta-lesson__dropzone-icon"><i class="bi bi-cloud-arrow-up"></i></span>
                                        <span class="peserta-lesson__dropzone-title">
                                            {{ $submission?->file_path ? __('Ganti berkas hasil pengerjaan') : __('Seret berkas ke sini atau klik untuk memilih') }}
                                        </span>
                                        <span class="peserta-lesson__dropzone-file text-muted fs-12" data-file-name></span>
                                    </label>
                                    <p class="mb-0 mt-2 text-muted fs-13">
                                        {{ __('Format: :formats — maks. :max MB', ['formats' => strtoupper($penugasanMimes), 'max' => $penugasanMaxMb]) }}
                                    </p>
                                    @error('submission_file')
                                        <div class="text-danger fs-13 mt-2">{{ $message }}</div>
                                    @enderror
                                @endif
                                <button type="submit" class="btn btn-primary btn-wave w-100 mt-3">
                                    <i class="bi bi-send-check me-1"></i>
                                    {{ $submission ? __('Perbarui jawaban') : __('Kirim jawaban') }}
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
                        @if($lesson->survey && $lesson->survey->questions->count() > 0)
                            <div class="mb-4">
                                <h3 class="mb-2">{{ $lesson->survey->title }}</h3>
                                @if($lesson->survey->description)
                                    <p class="text-muted fs-14">{{ $lesson->survey->description }}</p>
                                @endif
                            </div>

                            @if($surveyResponse)
                                <div class="alert alert-success d-flex align-items-center mb-4">
                                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                    <div>
                                        <strong>{{ __('Selesai!') }}</strong><br>
                                        {{ __('Anda telah mengisi kuesioner ini pada :date.', ['date' => $surveyResponse->updated_at?->translatedFormat('d F Y H:i')]) }}
                                    </div>
                                </div>
                            @endif

                            <form action="{{ route('peserta.kursus.lessons.survey.submit', [$course, $lesson]) }}" method="POST">
                                @csrf
                                <div class="survey-questions">
                                    @foreach($lesson->survey->questions as $index => $q)
                                        <div class="survey-question-item p-4 border rounded mb-3 {{ $errors->has('answers.'.$q->id) ? 'border-danger' : 'border-light' }}" style="background-color: #f8f9fa;">
                                            <div class="mb-3">
                                                <h5 class="fs-15 fw-medium mb-1">
                                                    {{ $index + 1 }}. {{ $q->question_text }}
                                                    @if($q->is_required)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </h5>
                                                @error('answers.'.$q->id)
                                                    <div class="text-danger fs-12 mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="ms-3">
                                                @php
                                                    $oldVal = old('answers.'.$q->id);
                                                    if (is_null($oldVal) && $surveyResponse) {
                                                        $answered = clone $surveyResponse->answers;
                                                        $ans = $answered->where('survey_question_id', $q->id);
                                                        
                                                        if ($q->type === 'checkbox') {
                                                            $oldVal = $ans->pluck('survey_option_id')->toArray();
                                                        } else if ($q->type === 'radio') {
                                                            $oldVal = $ans->first()?->survey_option_id;
                                                        } else if ($q->type === 'text' || $q->type === 'rating') {
                                                            $oldVal = $ans->first()?->answer_text;
                                                        }
                                                    }
                                                @endphp

                                                @if($q->type === 'text')
                                                    <textarea name="answers[{{ $q->id }}]" class="form-control" rows="3" placeholder="{{ __('Tuliskan jawaban Anda di sini...') }}" @required($q->is_required)>{{ $oldVal }}</textarea>
                                                
                                                @elseif($q->type === 'rating')
                                                    <div class="d-flex flex-wrap gap-4 align-items-center">
                                                        <span class="text-muted fs-13">{{ __('Sangat Buruk') }}</span>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <div class="form-check form-check-inline m-0">
                                                                <input class="form-check-input" type="radio" name="answers[{{ $q->id }}]" id="q_{{ $q->id }}_{{ $i }}" value="{{ $i }}" 
                                                                    @checked($oldVal == $i) @required($q->is_required)>
                                                                <label class="form-check-label ms-1" for="q_{{ $q->id }}_{{ $i }}">{{ $i }}</label>
                                                            </div>
                                                        @endfor
                                                        <span class="text-muted fs-13">{{ __('Sangat Baik') }}</span>
                                                    </div>

                                                @elseif($q->type === 'radio')
                                                    @foreach($q->options as $opt)
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="radio" name="answers[{{ $q->id }}]" id="q_{{ $q->id }}_{{ $opt->id }}" value="{{ $opt->id }}" 
                                                                @checked($oldVal == $opt->id) @required($q->is_required)>
                                                            <label class="form-check-label" for="q_{{ $q->id }}_{{ $opt->id }}">{{ $opt->option_text }}</label>
                                                        </div>
                                                    @endforeach

                                                @elseif($q->type === 'checkbox')
                                                    @foreach($q->options as $opt)
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" name="answers[{{ $q->id }}][]" id="q_{{ $q->id }}_{{ $opt->id }}" value="{{ $opt->id }}" 
                                                                @checked(is_array($oldVal) && in_array($opt->id, $oldVal))>
                                                            <label class="form-check-label" for="q_{{ $q->id }}_{{ $opt->id }}">{{ $opt->option_text }}</label>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary btn-wave px-4">
                                        <i class="bi bi-send-check me-2"></i>{{ $surveyResponse ? __('Perbarui Jawaban') : __('Kirim Kuesioner') }}
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="peserta-lesson__soon">
                                <i class="bi bi-clipboard2-data"></i>
                                <p class="mb-0">{{ __('Belum ada pertanyaan pada survey ini.') }}</p>
                            </div>
                        @endif
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
