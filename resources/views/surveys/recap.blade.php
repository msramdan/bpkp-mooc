@extends('layouts.app')

@section('title', __('Rekap Nilai & Analitik: ') . $survey->title)

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Rekap Nilai & Analitik') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('surveys.index') }}">{{ __('Bank Soal / Kuesioner') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $survey->title }}</li>
            </ol>
        </div>
        <div>
            <a href="{{ route('surveys.index') }}" class="btn btn-light btn-wave me-2">
                <i class="ri-arrow-left-line align-middle me-1"></i>{{ __('Kembali ke Daftar') }}
            </a>
            <a href="{{ route('surveys.builder', $survey) }}" class="btn btn-primary-light btn-wave">
                <i class="ri-list-settings-line align-middle me-1"></i>{{ __('Builder') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stat Cards -->
    <div class="row">
        <div class="col-xl-4 col-md-6">
            <div class="card custom-card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block mb-2 text-muted fs-13">{{ __('Total Peserta Mengisi') }}</span>
                            <h4 class="mb-0 fw-semibold">{{ number_format($overallStats['total_responses']) }} {{ __('Peserta') }}</h4>
                        </div>
                        <div class="avatar avatar-md bg-primary-transparent text-primary">
                            <i class="ri-group-line fs-20"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card custom-card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block mb-2 text-muted fs-13">{{ __('Rata-Rata Nilai PG / Esai') }}</span>
                            <h4 class="mb-0 fw-semibold text-success">
                                {{ $overallStats['average_pg'] !== null ? number_format($overallStats['average_pg'], 1) : '-' }}
                                <span class="fs-14 fw-normal text-muted">/</span>
                                <span class="text-warning">{{ $overallStats['average_essay'] !== null ? number_format($overallStats['average_essay'], 1) : '-' }}</span>
                            </h4>
                            <span class="fs-11 text-muted">{{ __('Skala 0 - 100, dihitung terpisah') }}</span>
                        </div>
                        <div class="avatar avatar-md bg-success-transparent text-success">
                            <i class="ri-pie-chart-2-line fs-20"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-12">
            <div class="card custom-card overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block mb-2 text-muted fs-13">{{ __('Menunggu Penilaian Esai') }}</span>
                            <h4 class="mb-0 fw-semibold {{ $overallStats['pending_essays'] > 0 ? 'text-warning' : 'text-muted' }}">
                                {{ number_format($overallStats['pending_essays']) }} {{ __('Jawaban') }}
                            </h4>
                        </div>
                        <div class="avatar avatar-md bg-warning-transparent text-warning">
                            <i class="ri-file-text-line fs-20"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analitik Tabs & Konten -->
    <div class="card custom-card">
        <div class="card-header border-bottom p-0">
            <ul class="nav nav-tabs nav-tabs-header nav-justified w-100" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-4 py-3 fw-medium" data-bs-toggle="tab" data-bs-target="#tab-participants" type="button" role="tab" aria-controls="tab-participants" aria-selected="true">
                        <i class="ri-user-follow-line me-1 align-middle"></i> {{ __('Rekap Nilai Per Peserta') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-3 fw-medium" data-bs-toggle="tab" data-bs-target="#tab-courses" type="button" role="tab" aria-controls="tab-courses" aria-selected="false">
                        <i class="ri-book-read-line me-1 align-middle"></i> {{ __('Rata-Rata Skor Per Kursus') }}
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- Tab 1: Rekap Per Peserta -->
                <div class="tab-pane fade show active" id="tab-participants" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h6 class="fw-semibold mb-0">{{ __('Daftar Riwayat Pengisian Peserta') }}</h6>
                        @if($responses->count() > 0)
                            <a href="{{ route('surveys.recap.export-participants', $survey) }}" class="btn btn-sm btn-success btn-wave">
                                <i class="ri-file-excel-line me-1"></i>{{ __('Ekspor Data Peserta (CSV/Excel)') }}
                            </a>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table text-nowrap table-bordered table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th scope="col" style="width: 50px;">{{ __('No') }}</th>
                                    <th scope="col">{{ __('Peserta') }}</th>
                                    <th scope="col">{{ __('Kursus & Materi') }}</th>
                                    <th scope="col" class="text-center">{{ __('Benar PG') }}</th>
                                    <th scope="col" class="text-center">{{ __('Nilai PG') }}</th>
                                    <th scope="col" class="text-center">{{ __('Nilai Esai') }}</th>
                                    <th scope="col" class="text-center">{{ __('Status Penilaian') }}</th>
                                    <th scope="col">{{ __('Waktu Submit') }}</th>
                                    <th scope="col" class="text-center">{{ __('Aksi / Penilaian Esai') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($responses as $index => $res)
                                    @php
                                        $nilaiAkhir = $res->max_possible_score > 0 ? ($res->total_score / $res->max_possible_score) * 100 : 0;
                                    @endphp
                                    <tr class="{{ $res->reset_at ? 'opacity-50' : '' }}">
                                        <td>{{ $responses->firstItem() + $index }}</td>
                                        <td>
                                            <div class="fw-medium">{{ $res->user?->name ?? __('User terhapus') }}</div>
                                            <span class="fs-12 text-muted">{{ $res->user?->email }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-truncate" style="max-width: 250px;">{{ $res->lesson?->module?->course?->judul ?? ($res->lesson?->course?->judul ?? __('N/A')) }}</div>
                                            <span class="fs-12 text-muted">{{ $res->lesson?->judul ?? $res->lesson?->title }}</span>
                                            <div class="mt-1 d-flex flex-wrap gap-1">
                                                <span class="badge bg-light text-dark border">{{ __('Percobaan ke-:n', ['n' => $res->attempt_number ?? 1]) }}</span>
                                                @if ($res->reset_at)
                                                    <span class="badge bg-secondary-transparent text-secondary" title="{{ $res->reset_reason }}">{{ __('Di-reset :date oleh :name', ['date' => $res->reset_at->translatedFormat('d M Y H:i'), 'name' => $res->resetter?->name ?? '-']) }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        @php $essayStatus = \App\Services\SurveyScoring::essayStatus($res); @endphp
                                        <td class="text-center">
                                            @if ($res->max_possible_score > 0)
                                                <span class="fs-15 fw-bold text-dark">{{ number_format($res->total_score / 100, 0) }}</span>
                                                <span class="text-muted fs-12"> / {{ number_format($res->max_possible_score / 100, 0) }} {{ __('soal') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($res->max_possible_score > 0)
                                                <span class="badge {{ $nilaiAkhir >= 70 ? 'bg-primary' : ($nilaiAkhir >= 50 ? 'bg-warning' : 'bg-danger') }} fs-13 px-3 py-2">
                                                    {{ number_format($nilaiAkhir, 1) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($essayStatus === 'graded')
                                                <span class="badge bg-warning-transparent text-dark fs-13 px-3 py-2" style="border:1px solid rgba(232,169,28,.5)">
                                                    {{ number_format((float) $res->essay_score, 1) }}
                                                </span>
                                            @elseif ($essayStatus === 'pending')
                                                <span class="fs-12 text-warning fw-semibold">{{ __('Belum dinilai') }} ({{ $res->essay_graded_count }}/{{ $res->essay_answered_count }})</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($res->grading_status === 'pending_essay')
                                                <span class="badge bg-warning-transparent text-warning">
                                                    <i class="ri-time-line me-1"></i>{{ __('Menunggu Esai') }}
                                                </span>
                                            @else
                                                <span class="badge bg-success-transparent text-success">
                                                    <i class="ri-check-line me-1"></i>{{ __('Selesai') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $res->created_at?->translatedFormat('d M Y, H:i') }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-primary-light btn-wave" data-bs-toggle="modal" data-bs-target="#modal-grade-{{ $res->id }}">
                                                <i class="ri-file-list-3-line me-1"></i>{{ __('Periksa / Nilai Esai') }}
                                            </button>
                                            @if (! $res->reset_at && $res->lesson)
                                                <form action="{{ route('surveys.recap.reset-attempts', $res) }}" method="POST" class="d-inline js-reset-attempt" data-peserta="{{ $res->user?->name }}" data-lesson="{{ $res->lesson?->judul }}">
                                                    @csrf
                                                    <input type="hidden" name="reason" value="">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-wave mt-1" title="{{ __('Reset percobaan peserta ini agar bisa mengerjakan ulang') }}">
                                                        <i class="ri-restart-line me-1"></i>{{ __('Reset') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center p-4 text-muted">
                                            <i class="ri-inbox-archive-line fs-2 d-block mb-2"></i>
                                            {{ __('Belum ada data respons / pengisian pada soal atau kuesioner ini.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $responses->links() }}
                    </div>
                </div>

                <!-- Tab 2: Rata-Rata Per Kursus -->
                <div class="tab-pane fade" id="tab-courses" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h6 class="fw-semibold mb-0">{{ __('Rata-Rata Pencapaian Skor Berdasarkan Kursus') }}</h6>
                        @if(count($courseRecap) > 0)
                            <a href="{{ route('surveys.recap.export-courses', $survey) }}" class="btn btn-sm btn-success btn-wave">
                                <i class="ri-file-excel-line me-1"></i>{{ __('Ekspor Data Kursus (CSV/Excel)') }}
                            </a>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table text-nowrap table-bordered table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th scope="col" style="width: 50px;">{{ __('No') }}</th>
                                    <th scope="col">{{ __('Nama Kursus') }}</th>
                                    <th scope="col" class="text-center">{{ __('Jumlah Peserta') }}</th>
                                    <th scope="col" class="text-center">{{ __('Rata-Rata Nilai PG') }}</th>
                                    <th scope="col" class="text-center">{{ __('Rata-Rata Nilai Esai') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courseRecap as $idx => $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-medium">{{ $row['course_title'] }}</td>
                                        <td class="text-center">{{ number_format($row['total_participants']) }} {{ __('Peserta') }}</td>
                                        <td class="text-center">
                                            @if ($row['average_pg'] !== null)
                                                <div class="progress progress-xs mb-1 d-inline-block w-50 align-middle me-2">
                                                    <div class="progress-bar {{ $row['average_pg'] >= 70 ? 'bg-success' : ($row['average_pg'] >= 50 ? 'bg-warning' : 'bg-danger') }}" role="progressbar" style="width: {{ $row['average_pg'] }}%" aria-valuenow="{{ $row['average_pg'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="fw-semibold">{{ number_format($row['average_pg'], 1) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-semibold">{{ $row['average_essay'] !== null ? number_format($row['average_essay'], 1) : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center p-4 text-muted">{{ __('Belum ada data kursus untuk kuesioner ini.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals untuk Pemeriksaan & Penilaian Esai -->
    @foreach($responses as $res)
        <div class="modal fade" id="modal-grade-{{ $res->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form action="{{ route('surveys.recap.grade-essay', $res) }}" method="POST" class="modal-content">
                    @csrf
                        <div class="modal-header bg-light">
                            <div>
                                <h5 class="modal-title mb-1">{{ __('Lembar Jawaban & Penilaian Esai') }}</h5>
                                <span class="text-muted fs-13">{{ $res->user?->name }} — {{ $res->lesson?->module?->course?->judul ?? ($res->lesson?->course?->judul ?? '-') }}</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info py-2 d-flex align-items-center mb-4">
                                <i class="ri-information-line fs-18 me-2"></i>
                                <div>
                                    {{ __('Soal PG (pilihan ganda / checkbox) dinilai otomatis menjadi Nilai PG. Esai diberi nilai 0 - 100 dan direkap TERPISAH sebagai Nilai Esai (rata-rata), tidak mengubah Nilai PG.') }}
                                </div>
                            </div>

                            @php $hasEssayInput = false; @endphp
                            @foreach($survey->questions as $qIndex => $q)
                                @php
                                    $ans = $res->answers->where('survey_question_id', $q->id);
                                    $firstAns = $ans->first();
                                    $hasKey = in_array($q->type, ['radio', 'checkbox'], true) && $q->options->contains('is_correct', true);
                                    $isCorrect = $hasKey && $ans->isNotEmpty() && $ans->sum('score') > 0;
                                @endphp
                                <div class="grade-q p-3 border rounded mb-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <h6 class="fw-semibold mb-0">{{ $qIndex + 1 }}. {{ $q->question_text }}</h6>
                                        <span class="badge bg-dark-transparent text-dark">{{ $q->type === 'text' ? __('ESAI') : ($hasKey ? __('PG') : strtoupper($q->type)) }}</span>
                                    </div>

                                    <div class="grade-q__answer">
                                        @if($q->type === 'radio')
                                            <span class="grade-q__label">{{ __('Pilihan peserta') }}</span>
                                            {{ $firstAns?->option?->option_text ?? __('Tidak menjawab') }}
                                        @elseif($q->type === 'checkbox')
                                            <span class="grade-q__label">{{ __('Pilihan peserta') }}</span>
                                            @if ($ans->isEmpty())
                                                {{ __('Tidak menjawab') }}
                                            @else
                                                <ul class="mb-0 ps-3">
                                                    @foreach($ans as $cAns)
                                                        <li>{{ $cAns->option?->option_text ?? '-' }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        @elseif($q->type === 'rating')
                                            <span class="grade-q__label">{{ __('Rating') }}</span>
                                            {{ $firstAns?->answer_text ? $firstAns->answer_text.' / 5' : __('Tidak menjawab') }}
                                        @elseif($q->type === 'text')
                                            <span class="grade-q__label">{{ __('Jawaban esai peserta') }}</span>
                                            <p class="mb-0" style="white-space: pre-wrap;">{{ filled($firstAns?->answer_text) ? $firstAns->answer_text : __('(Tidak menjawab / kosong)') }}</p>
                                        @endif
                                    </div>

                                    @if ($hasKey)
                                        <div class="mt-2 fs-13">
                                            @if ($isCorrect)
                                                <span class="badge bg-success-transparent text-success"><i class="ri-check-line me-1"></i>{{ __('Benar (otomatis)') }}</span>
                                            @else
                                                <span class="badge bg-danger-transparent text-danger"><i class="ri-close-line me-1"></i>{{ __('Salah (otomatis)') }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($q->type === 'text' && $firstAns && filled($firstAns->answer_text))
                                        @php $hasEssayInput = true; @endphp
                                        <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                                            <label for="score-{{ $firstAns->id }}" class="form-label fw-semibold mb-0 text-primary">{{ __('Nilai esai (0 - 100)') }}</label>
                                            <input type="number" id="score-{{ $firstAns->id }}" name="scores[{{ $firstAns->id }}]" class="form-control text-center fw-bold" style="max-width: 110px;"
                                                   min="0" max="100" step="1" inputmode="numeric" placeholder="0 - 100"
                                                   value="{{ $firstAns->is_graded ? (int) $firstAns->score : '' }}">
                                            @if ($firstAns->is_graded)
                                                <span class="badge bg-success-transparent text-success">{{ __('Sudah dinilai') }}</span>
                                            @else
                                                <span class="badge bg-warning-transparent text-warning">{{ __('Belum dinilai') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer">
                            @if ($hasEssayInput)
                                <span class="me-auto fs-12 text-muted">{{ __('Kosongkan bila belum ingin dinilai.') }}</span>
                            @endif
                            <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                            @if ($hasEssayInput)
                                <button type="submit" class="btn btn-primary btn-wave">
                                    <i class="ri-save-line me-1"></i>{{ __('Simpan Nilai Esai') }}
                                </button>
                            @endif
                        </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection

@push('css')
    <style>
        .grade-q { background: rgba(var(--light-rgb, 243,246,248), .45); }
        .grade-q__answer { background: #fff; color: #212529; border: 1px solid rgba(0,0,0,.08); border-radius: .5rem; padding: .6rem .8rem; font-size: 14px; }
        .grade-q__label { display: block; font-size: 11px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: #6c757d; margin-bottom: 2px; }
        [data-theme-mode=dark] .grade-q__answer { background: rgba(255,255,255,.04); color: #e9ecef; border-color: rgba(255,255,255,.1); }
    </style>
@endpush

@push('scripts')
    <script>
        // Konfirmasi reset percobaan + alasan (opsional), dicatat di data jawaban.
        document.querySelectorAll('.js-reset-attempt').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === '1') return;
                e.preventDefault();
                var text = 'Reset semua percobaan aktif ' + (form.dataset.peserta || 'peserta') + ' pada "' + (form.dataset.lesson || '') + '"? Jawaban lama tetap tersimpan sebagai riwayat, dan peserta bisa mengerjakan ulang.';
                var submit = function (reason) {
                    form.querySelector('[name="reason"]').value = reason || '';
                    form.dataset.confirmed = '1';
                    form.submit();
                };
                if (window.Swal) {
                    Swal.fire({
                        title: 'Reset percobaan?',
                        text: text,
                        input: 'text',
                        inputPlaceholder: 'Alasan reset (opsional)',
                        inputAttributes: { maxlength: 255 },
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, reset',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc2626',
                    }).then(function (r) { if (r.isConfirmed) submit(r.value); });
                } else if (window.confirm(text)) {
                    submit(window.prompt('Alasan reset (opsional)') || '');
                }
            });
        });
    </script>
@endpush
