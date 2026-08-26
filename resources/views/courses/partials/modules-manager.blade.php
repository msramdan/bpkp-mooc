@php
    use App\Support\ActivityTypes;
@endphp

@can('course edit')
    <div class="topic-manager__header">
        <div>
            <h3 class="topic-manager__title">{{ __('Topik kursus') }}</h3>
            <p class="topic-manager__hint">{{ __('Geser ikon untuk mengatur urutan topik.') }}</p>
        </div>
        <button type="button" class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#topicCreateModal">
            <i class="ri-add-line me-1"></i>{{ __('Tambah topik') }}
        </button>
    </div>
@endcan

<div id="topics-sortable"
    class="topics-sortable"
    @can('course edit')
        data-reorder-url="{{ route('courses.modules.reorder', $course) }}"
    @endcan>
@forelse ($course->modules as $module)
    <div class="card custom-card mb-3 topic-card" data-topic-id="{{ $module->id }}">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 min-w-0">
                @can('course edit')
                    <button type="button" class="topic-drag-handle" title="{{ __('Geser untuk urutkan') }}" aria-label="{{ __('Geser untuk urutkan') }}">
                        <i class="ri-draggable" aria-hidden="true"></i>
                    </button>
                @endcan
                <span class="topic-order-num">{{ $module->urutan }}</span>
                <span class="card-title mb-0 text-truncate">{{ $module->judul }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                @can('course edit')
                    <button type="button" class="btn btn-sm btn-primary btn-wave"
                        data-bs-toggle="modal"
                        data-bs-target="#activityTypeModal"
                        data-topic-id="{{ $module->id }}"
                        data-topic-store="{{ route('courses.modules.lessons.store', [$course, $module]) }}">
                        <i class="ri-add-line me-1"></i>{{ __('Tambah aktivitas') }}
                    </button>
                    <form action="{{ route('courses.modules.destroy', [$course, $module]) }}" method="post" class="d-inline">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn btn-sm btn-danger-light btn-wave js-delete-confirm" title="{{ __('Hapus topik') }}">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </form>
                @endcan
            </div>
        </div>
        <div class="card-body">
            @can('course edit')
                <form method="POST" action="{{ route('courses.modules.update', [$course, $module]) }}" class="row g-2 mb-3 topic-inline-form">
                    @csrf
                    @method('PUT')
                    <div class="col-md-5">
                        <input type="text" name="judul" class="form-control" value="{{ $module->judul }}" required maxlength="255">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="deskripsi" class="form-control" value="{{ $module->deskripsi }}" placeholder="{{ __('Deskripsi') }}" maxlength="2000">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary btn-wave w-100">{{ __('Simpan') }}</button>
                    </div>
                    <input type="hidden" name="durasi_menit" value="{{ $module->durasi_menit }}">
                </form>
            @elseif ($module->deskripsi)
                <p class="text-muted mb-3">{{ $module->deskripsi }}</p>
            @endcan

            <div class="topic-activity-list">
                @forelse ($module->lessons as $lesson)
                    <div class="topic-activity-item">
                        <div class="topic-activity-item__icon" style="--act-color: {{ $lesson->typeColor() }}">
                            <i class="bi {{ $lesson->iconClass() }}"></i>
                        </div>
                        <div class="topic-activity-item__body min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <strong class="text-truncate">{{ $lesson->judul }}</strong>
                                <span class="badge bg-light text-muted">{{ $lesson->typeLabel() }}</span>
                            </div>
                            @if ($lesson->sanitizedBody())
                                <div class="topic-activity-item__desc text-muted fs-12 mt-1">{!! \Illuminate\Support\Str::limit(strip_tags($lesson->sanitizedBody()), 120) !!}</div>
                            @endif
                            @if ($lesson->externalUrl())
                                @include('courses.partials.activity-file-preview', [
                                    'url' => $lesson->externalUrl(),
                                    'title' => $lesson->judul,
                                    'compact' => false,
                                ])
                            @endif
                        </div>
                        @can('course edit')
                            <div class="topic-activity-item__actions">
                                <button type="button"
                                    class="btn btn-sm btn-light btn-wave js-activity-edit"
                                    title="{{ __('Ubah aktivitas') }}"
                                    data-activity='@json([
                                        "update_url" => route("courses.modules.lessons.update", [$course, $module, $lesson]),
                                        "tipe" => $lesson->normalizedType(),
                                        "type_label" => $lesson->typeLabel(),
                                        "judul" => $lesson->judul,
                                        "durasi_menit" => (int) ($lesson->durasi_menit ?? 10),
                                        "body" => (string) ($lesson->body ?? ""),
                                        "file_url" => (string) ($lesson->file_url ?? ""),
                                        "video_url" => (string) ($lesson->video_url ?? ""),
                                        "external_url" => (string) ($lesson->externalUrl() ?? ""),
                                        "media_url" => (string) ($lesson->resolveMediaUrlPublic() ?? $lesson->externalUrl() ?? ""),
                                        "survey_id" => (string) ($lesson->survey_id ?? ""),
                                        "certificate_template_id" => (string) ($lesson->certificate_template_id ?? ""),
                                        "prerequisite_survey_id" => (string) ($lesson->prerequisite_survey_id ?? ""),
                                        "passing_grade" => (int) ($lesson->passing_grade ?? 0),
                                        "require_all_lessons" => (bool) ($lesson->require_all_lessons ?? true),
                                        "assignment_allow_text" => (bool) $lesson->assignment_allow_text,
                                        "assignment_allow_file" => (bool) $lesson->assignment_allow_file,
                                        "assignment_allow_link" => (bool) $lesson->assignment_allow_link,
                                        "is_preview" => (bool) $lesson->is_preview,
                                        "is_required" => (bool) $lesson->is_required,
                                        "has_file" => filled($lesson->file_url),
                                        "has_video" => filled($lesson->video_url),
                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)'>
                                    <i class="ri-edit-line"></i>
                                </button>
                                <form action="{{ route('courses.modules.lessons.destroy', [$course, $module, $lesson]) }}"
                                    method="post" class="d-inline">
                                    @csrf
                                    @method('delete')
                                    <button type="button" class="btn btn-sm btn-danger-light btn-wave js-delete-confirm">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>
                        @endcan
                    </div>
                @empty
                    <p class="text-muted text-center py-3 mb-0">{{ __('Belum ada aktivitas di topik ini.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@empty
    <div class="topic-empty">
        <i class="bi bi-journal-plus"></i>
        <p class="mb-1 fw-medium">{{ __('Belum ada topik') }}</p>
        <p class="mb-3 fs-13 text-muted">{{ __('Tambahkan topik terlebih dahulu, lalu isi dengan aktivitas (Berkas, Video, URL).') }}</p>
        @can('course edit')
            <button type="button" class="btn btn-wave topic-empty__cta" data-bs-toggle="modal" data-bs-target="#topicCreateModal">
                <span class="topic-empty__cta-icon"><i class="ri-add-line"></i></span>
                <span>{{ __('Tambah topik') }}</span>
            </button>
        @endcan
    </div>
@endforelse
</div>

@can('course edit')
    {{-- Modal: tambah topik --}}
    <div class="modal fade" id="topicCreateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('courses.modules.store', $course) }}">
                    @csrf
                    <input type="hidden" name="durasi_menit" value="0">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Tambah topik') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body topic-modal-form">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Nama topik') }} <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required maxlength="255"
                                placeholder="{{ __('Contoh: Persiapan, Pembelajaran, Sertifikat') }}" autofocus>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">{{ __('Deskripsi') }}</label>
                            <textarea name="deskripsi" class="form-control" rows="3" maxlength="2000"
                                placeholder="{{ __('Opsional') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                        <button type="submit" class="btn btn-primary btn-wave">{{ __('Simpan') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: pilih tipe aktivitas --}}
    <div class="modal fade" id="activityTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Tambahkan aktivitas atau sumber') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="search" class="form-control mb-3" id="activityTypeSearch" placeholder="{{ __('Cari') }}">
                    <div class="activity-type-grid" id="activityTypeGrid">
                        @foreach (ActivityTypes::pickerPalette() as $item)
                            <button type="button"
                                class="activity-type-tile {{ $item['enabled'] ? '' : 'is-disabled' }}"
                                data-type-key="{{ $item['key'] }}"
                                data-type-label="{{ $item['label'] }}"
                                data-enabled="{{ $item['enabled'] ? '1' : '0' }}"
                                data-search="{{ strtolower($item['label'].' '.$item['key']) }}"
                                @disabled(! $item['enabled'])
                                style="--tile-color: {{ $item['color'] }}">
                                <span class="activity-type-tile__icon"><i class="bi {{ $item['icon'] }}"></i></span>
                                <span class="activity-type-tile__label">{{ $item['label'] }}</span>
                                @unless ($item['enabled'])
                                    <span class="activity-type-tile__badge">{{ __('Segera') }}</span>
                                @endunless
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: form aktivitas (Umum) --}}
    <div class="modal fade" id="activityFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="POST" id="activityCreateForm" enctype="multipart/form-data" class="modal-content">
                @csrf
                <input type="hidden" name="_method" id="activityFormMethod" value="PUT" disabled>
                <input type="hidden" name="tipe" id="activityFormType">
                <input type="hidden" name="is_required" id="activityFormIsRequiredFixed" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="activityFormModeLabel">{{ __('Tambah') }}</span>:
                        <span id="activityFormTypeLabel"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-muted text-uppercase fs-11 mb-3">{{ __('Umum') }}</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-8" id="activityFieldNamaCol">
                            <label class="form-label">{{ __('Nama') }} <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required maxlength="255" placeholder="{{ __('Judul aktivitas...') }}">
                        </div>
                        <div class="col-md-4" id="activityFieldDurasi">
                            <label class="form-label">{{ __('Estimasi (Menit)') }} <span class="text-danger">*</span></label>
                            <input type="number" name="durasi_menit" id="activityInputDurasi" class="form-control" value="10" min="0" max="9999">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Deskripsi') }}</label>
                        <textarea name="body" class="form-control" rows="4" placeholder="{{ __('Deskripsi aktivitas...') }}"></textarea>
                    </div>
                    <div class="mb-3 d-none" id="activityCurrentMediaWrap">
                        <label class="form-label">{{ __('Lampiran saat ini') }}</label>
                        <div id="activityCurrentMedia"></div>
                        <div class="form-text">{{ __('Kosongkan unggahan baru jika tidak ingin mengganti lampiran.') }}</div>
                    </div>
                    <div class="mb-3 d-none" id="activityFieldVideo">
                        @php
                            $videoMaxMb = round(((int) config('mooc.video_max_kb', 51200)) / 1024, 1);
                            $videoMimes = implode(', ', (array) config('mooc.video_mimes', ['mp4', 'webm']));
                        @endphp
                        <label class="form-label">{{ __('Unggah video') }} <span class="text-danger activity-required-mark">*</span></label>
                        <input type="file" name="video_file" class="form-control js-upload-size"
                            accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.avi,.mkv,.m4v"
                            data-max-kb="{{ (int) config('mooc.video_max_kb', 51200) }}"
                            data-label="{{ __('Video') }}">
                        <div class="form-text">
                            {{ __('Maksimal :max MB. Format: :formats', ['max' => $videoMaxMb, 'formats' => $videoMimes]) }}
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="activityFieldUrl">
                        <label class="form-label">{{ __('Tautan URL') }} <span class="text-danger">*</span></label>
                        <input type="url" name="file_url" class="form-control" placeholder="https://contoh.com/halaman"
                            inputmode="url" autocomplete="url">
                        <div class="form-text">{{ __('Masukkan tautan lengkap (http/https) yang akan dibuka peserta.') }}</div>
                    </div>
                    @php
                        $berkasMaxMb = round(((int) config('mooc.berkas_max_kb', 10240)) / 1024, 1);
                        $berkasMimes = implode(', ', (array) config('mooc.berkas_mimes', ['pdf']));
                        $h5pMaxMb = round(((int) config('mooc.h5p_max_kb', 204800)) / 1024, 1);
                    @endphp
                    <div class="mb-3 d-none" id="activityFieldBerkas"
                        data-berkas-max-kb="{{ (int) config('mooc.berkas_max_kb', 10240) }}"
                        data-penugasan-max-kb="{{ (int) config('mooc.penugasan_max_kb', 10240) }}"
                        data-h5p-max-kb="{{ (int) config('mooc.h5p_max_kb', 204800) }}"
                        data-berkas-help="{{ __('Maksimal :max MB. Format: :formats', ['max' => $berkasMaxMb, 'formats' => $berkasMimes]) }}"
                        data-h5p-help="{{ __('Maksimal :max MB. Format: .h5p', ['max' => $h5pMaxMb]) }}">
                        <label class="form-label">{{ __('Unggah berkas') }} <span class="text-danger activity-required-mark">*</span></label>
                        <input type="file" name="berkas_file" class="form-control js-upload-size"
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.txt,.png,.jpg,.jpeg,.webp"
                            data-max-kb="{{ (int) config('mooc.berkas_max_kb', 10240) }}"
                            data-label="{{ __('Berkas') }}">
                        <div class="form-text">
                            {{ __('Maksimal :max MB. Format: :formats', ['max' => $berkasMaxMb, 'formats' => $berkasMimes]) }}
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="activityFieldPenugasanRequirements">
                        <label class="form-label">{{ __('Kebutuhan pengumpulan peserta') }} <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-3">
                            <label class="form-check mb-0">
                                <input type="hidden" name="assignment_allow_text" value="0">
                                <input type="checkbox" name="assignment_allow_text" value="1" class="form-check-input" id="activityAssignText">
                                <span class="form-check-label">{{ __('Uraian jawaban') }}</span>
                            </label>
                            <label class="form-check mb-0">
                                <input type="hidden" name="assignment_allow_file" value="0">
                                <input type="checkbox" name="assignment_allow_file" value="1" class="form-check-input" id="activityAssignFile" checked>
                                <span class="form-check-label">{{ __('Unggah dokumen') }}</span>
                            </label>
                            <label class="form-check mb-0">
                                <input type="hidden" name="assignment_allow_link" value="0">
                                <input type="checkbox" name="assignment_allow_link" value="1" class="form-check-input" id="activityAssignLink">
                                <span class="form-check-label">{{ __('Tautan jawaban') }}</span>
                            </label>
                        </div>
                        <div class="form-text">{{ __('Centang apa saja yang wajib dikirim peserta untuk aktivitas penugasan ini.') }}</div>
                    </div>
                    <div class="mb-3 d-none" id="activityFieldSurvey">
                        <label class="form-label">{{ __('Pilih Master Survey / Kuesioner') }} <span class="text-danger">*</span></label>
                        <select name="survey_id" class="form-select" id="surveySelectInput">
                            <option value="">{{ __('-- Pilih Survey --') }}</option>
                            @foreach($surveys ?? [] as $svy)
                                <option value="{{ $svy->id }}">{{ $svy->title }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            {{ __('Pilih kuesioner dari Bank Survey. Jika belum ada, buat di menu Data Utama > Bank Survey.') }}
                        </div>
                    </div>
                    <div class="mb-3 d-none border rounded p-3 bg-light shadow-sm" id="activityFieldSertifikat">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-award-fill text-warning me-2"></i>{{ __('Gerbang Kelulusan Sertifikat') }}</h6>
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-13">{{ __('Pilih Master Template Sertifikat') }}</label>
                            <select name="certificate_template_id" class="form-select" id="activityCertTemplate">
                                <option value="">{{ __('Gunakan Template Default Sistem') }}</option>
                                @foreach($certificateTemplates ?? [] as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->title }}</option>
                                @endforeach
                            </select>
                            <div class="form-text fs-11">{{ __('Template master diatur dari menu Admin Data Utama > Template Sertifikat.') }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-13">{{ __('Prasyarat Survey / Kuesioner Evaluasi') }}</label>
                            <select name="prerequisite_survey_id" class="form-select" id="activityCertSurvey">
                                <option value="">{{ __('-- Tanpa Prasyarat Kuesioner --') }}</option>
                                @foreach($surveys ?? [] as $svy)
                                    <option value="{{ $svy->id }}">{{ $svy->title }}</option>
                                @endforeach
                            </select>
                            <div class="form-text fs-11">{{ __('Jika dipilih, tombol cetak sertifikat terkunci sebelum peserta mengisi kuesioner evaluasi ini.') }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-13">{{ __('Passing Grade Minimal (0 - 100)') }}</label>
                            <input type="number" name="passing_grade" id="activityPassingGrade" class="form-control" value="70" min="0" max="100">
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input type="hidden" name="require_all_lessons" value="0">
                            <input type="checkbox" name="require_all_lessons" value="1" class="form-check-input" id="req_all_create" checked>
                            <label class="form-check-label fw-medium fs-13" for="req_all_create">{{ __('Kunci sertifikat hingga peserta menyelesaikan 100% materi kursus') }}</label>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-3 d-none" id="activityFieldFlags">
                        <div class="form-check">
                            <input type="checkbox" name="is_preview" value="1" class="form-check-input" id="activityIsPreview">
                            <label class="form-check-label" for="activityIsPreview">{{ __('Preview') }}</label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_required" value="0" id="activityIsRequiredHidden" disabled>
                            <input type="checkbox" name="is_required" value="1" class="form-check-input" id="activityIsRequired" checked>
                            <label class="form-check-label" for="activityIsRequired">{{ __('Wajib') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-primary btn-wave" id="activityFormSubmitBtn">{{ __('Simpan') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Preview berkas/video aktivitas admin --}}
    <div class="modal fade" id="activityFilePreviewModal" tabindex="-1" aria-labelledby="activityFilePreviewModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content activity-file-modal">
                <div class="modal-header border-0 pb-0">
                    <div class="min-w-0">
                        <span class="activity-file-modal__kicker">{{ __('Preview berkas') }}</span>
                        <h5 class="modal-title text-truncate" id="activityFilePreviewModalTitle">—</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Tutup') }}"></button>
                </div>
                <div class="modal-body activity-file-modal__body" id="activityFilePreviewModalBody">
                    <div class="activity-file-modal__placeholder text-muted">{{ __('Memuat preview...') }}</div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <a href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-wave" id="activityFilePreviewOpenTab">
                        <i class="ri-external-link-line me-1"></i>{{ __('Buka di tab baru') }}
                    </a>
                    <button type="button" class="btn btn-light btn-wave" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                </div>
            </div>
        </div>
    </div>
@endcan
