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
                            <span class="d-block mb-2 text-muted fs-13">{{ __('Rata-Rata Skor Keseluruhan') }}</span>
                            <h4 class="mb-0 fw-semibold text-success">{{ number_format($overallStats['average_score'], 1) }} <span class="fs-14 fw-normal text-muted">{{ __('Poin') }}</span></h4>
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
                                    <th scope="col" class="text-center">{{ __('Skor Diperoleh') }}</th>
                                    <th scope="col" class="text-center">{{ __('Nilai (0 - 100)') }}</th>
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
                                    <tr>
                                        <td>{{ $responses->firstItem() + $index }}</td>
                                        <td>
                                            <div class="fw-medium">{{ $res->user?->name ?? __('User terhapus') }}</div>
                                            <span class="fs-12 text-muted">{{ $res->user?->email }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-truncate" style="max-width: 250px;">{{ $res->lesson?->module?->course?->judul ?? ($res->lesson?->course?->judul ?? __('N/A')) }}</div>
                                            <span class="fs-12 text-muted">{{ $res->lesson?->judul ?? $res->lesson?->title }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fs-15 fw-bold text-dark">{{ number_format($res->total_score, 0) }}</span>
                                            <span class="text-muted fs-12"> / {{ number_format($res->max_possible_score, 0) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $nilaiAkhir >= 70 ? 'bg-primary' : ($nilaiAkhir >= 50 ? 'bg-warning' : 'bg-danger') }} fs-13 px-3 py-2">
                                                {{ number_format($nilaiAkhir, 1) }}
                                            </span>
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
                                        <td>{{ $res->updated_at?->translatedFormat('d M Y, H:i') }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-primary-light btn-wave" data-bs-toggle="modal" data-bs-target="#modal-grade-{{ $res->id }}">
                                                <i class="ri-file-list-3-line me-1"></i>{{ __('Periksa / Nilai Esai') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center p-4 text-muted">
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
                                    <th scope="col" class="text-center">{{ __('Rata-Rata Skor Diperoleh') }}</th>
                                    <th scope="col" class="text-center">{{ __('Rata-Rata Skor Maksimal') }}</th>
                                    <th scope="col" class="text-center">{{ __('Persentase Keberhasilan') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courseRecap as $idx => $row)
                                    @php
                                        $percentage = $row['max_possible'] > 0 ? ($row['average_score'] / $row['max_possible']) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-medium">{{ $row['course_title'] }}</td>
                                        <td class="text-center">{{ number_format($row['total_participants']) }} {{ __('Peserta') }}</td>
                                        <td class="text-center fw-semibold text-primary">{{ number_format($row['average_score'], 1) }}</td>
                                        <td class="text-center">{{ number_format($row['max_possible'], 1) }}</td>
                                        <td class="text-center">
                                            <div class="progress progress-xs mb-1 d-inline-block w-50 align-middle me-2">
                                                <div class="progress-bar {{ $percentage >= 70 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="fs-12">{{ number_format($percentage, 1) }}%</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center p-4 text-muted">{{ __('Belum ada data kursus untuk kuesioner ini.') }}</td>
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
                                    {{ __('Pertanyaan objektif (Radio, Checkbox, Rating) dinilai otomatis oleh sistem. Untuk pertanyaan Esai (Isian Teks), silakan beri nilai 0 sampai 100.') }}
                                </div>
                            </div>

                            @foreach($survey->questions as $qIndex => $q)
                                @php
                                    $ans = $res->answers->where('survey_question_id', $q->id);
                                    $firstAns = $ans->first();
                                @endphp
                                <div class="p-3 border rounded mb-3 bg-light-transparent">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <h6 class="fw-semibold mb-0">{{ $qIndex + 1 }}. {{ $q->question_text }}</h6>
                                        <span class="badge bg-dark-transparent">{{ strtoupper($q->type) }}</span>
                                    </div>

                                    <!-- Tampilkan Jawaban Peserta -->
                                    <div class="mb-2 ms-3">
                                        @if($q->type === 'radio')
                                            <div class="p-2 bg-white rounded border fs-14">
                                                <strong>{{ __('Pilihan Peserta:') }}</strong> {{ $firstAns?->option?->option_text ?? __('Tidak menjawab') }}
                                            </div>
                                            <div class="mt-1 fs-13 text-muted">
                                                {{ __('Skor Otomatis:') }} <strong class="text-primary">{{ $firstAns?->score ?? 0 }} Poin</strong>
                                            </div>
                                        @elseif($q->type === 'checkbox')
                                            <div class="p-2 bg-white rounded border fs-14">
                                                <strong>{{ __('Pilihan Peserta:') }}</strong><br>
                                                <ul class="mb-0 ps-3">
                                                    @foreach($ans as $cAns)
                                                        <li>{{ $cAns->option?->option_text ?? '-' }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="mt-1 fs-13 text-muted">
                                                {{ __('Skor Otomatis (All-or-Nothing):') }} <strong class="text-primary">{{ $ans->sum('score') }} Poin</strong>
                                            </div>
                                        @elseif($q->type === 'rating')
                                            <div class="p-2 bg-white rounded border fs-14">
                                                <strong>{{ __('Rating Pilihan:') }}</strong> {{ $firstAns?->answer_text ?? '-' }} / 5 Bintang
                                            </div>
                                            <div class="mt-1 fs-13 text-muted">
                                                {{ __('Skor Otomatis:') }} <strong class="text-primary">{{ $firstAns?->score ?? 0 }} Poin</strong>
                                            </div>
                                        @elseif($q->type === 'text')
                                            <div class="p-3 bg-white rounded border fs-14 mb-2">
                                                <strong>{{ __('Jawaban Esai Peserta:') }}</strong><br>
                                                <p class="mb-0 mt-1 fst-italic text-dark" style="white-space: pre-wrap;">{{ $firstAns?->answer_text ?: __('(Tidak menjawab / kosong)') }}</p>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 mt-2">
                                                <label class="form-label fw-semibold mb-0 text-primary">{{ __('Beri Nilai Esai (0 - 100):') }}</label>
                                                @if($firstAns)
                                                    <input type="number" name="scores[{{ $firstAns->id }}]" class="form-control text-center fw-bold" style="max-width: 100px;" min="0" max="100" value="{{ $firstAns->score ?? 0 }}" required>
                                                @else
                                                    <span class="text-danger fs-12">{{ __('Peserta tidak menjawab soal ini.') }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                            <button type="submit" class="btn btn-primary btn-wave">
                                <i class="ri-save-line me-1"></i>{{ __('Simpan Nilai Esai & Update Skor') }}
                            </button>
                        </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection
