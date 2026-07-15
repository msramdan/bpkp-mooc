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
                                <div class="fs-12 text-muted text-truncate mt-1">{{ $lesson->externalUrl() }}</div>
                            @endif
                        </div>
                        @can('course edit')
                            <div class="topic-activity-item__actions">
                                <button type="button" class="btn btn-sm btn-light btn-wave"
                                    data-bs-toggle="collapse" data-bs-target="#edit-activity-{{ $lesson->id }}">
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
                    @can('course edit')
                        <div class="collapse mb-3" id="edit-activity-{{ $lesson->id }}">
                            <form method="POST" action="{{ route('courses.modules.lessons.update', [$course, $module, $lesson]) }}"
                                class="border rounded p-3 bg-light" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="tipe" value="{{ $lesson->normalizedType() }}">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label fs-12">{{ __('Nama') }}</label>
                                        <input type="text" name="judul" class="form-control form-control-sm" value="{{ $lesson->judul }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fs-12">{{ __('Tipe') }}</label>
                                        <input type="text" class="form-control form-control-sm" value="{{ $lesson->typeLabel() }}" disabled>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fs-12">{{ __('Deskripsi') }}</label>
                                        <textarea name="body" class="form-control form-control-sm" rows="2">{{ $lesson->body }}</textarea>
                                    </div>
                                    @if ($lesson->normalizedType() === 'video')
                                        @php
                                            $videoMaxMb = round(((int) config('mooc.video_max_kb', 51200)) / 1024, 1);
                                        @endphp
                                        <div class="col-12">
                                            <label class="form-label fs-12">{{ __('Unggah video') }}</label>
                                            @if ($lesson->video_url)
                                                <div class="mb-1 fs-12">
                                                    <a href="{{ $lesson->resolveMediaUrlPublic() }}" target="_blank" rel="noopener">
                                                        <i class="ri-play-circle-line me-1"></i>{{ __('Lihat video saat ini') }}
                                                    </a>
                                                </div>
                                            @endif
                                            <input type="file" name="video_file" class="form-control form-control-sm js-upload-size"
                                                accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.avi,.mkv,.m4v"
                                                data-max-kb="{{ (int) config('mooc.video_max_kb', 51200) }}"
                                                data-label="{{ __('Video') }}"
                                                @required(! $lesson->video_url)>
                                            <div class="form-text fs-11">
                                                {{ __('Kosongkan jika tidak diganti. Maks. :max MB (MP4, WebM, MOV, AVI, MKV).', ['max' => $videoMaxMb]) }}
                                            </div>
                                        </div>
                                    @elseif ($lesson->normalizedType() === 'url')
                                        <div class="col-12">
                                            <label class="form-label fs-12">{{ __('Tautan URL') }}</label>
                                            <input type="url" name="file_url" class="form-control form-control-sm"
                                                value="{{ $lesson->file_url }}" placeholder="https://contoh.com/halaman"
                                                inputmode="url" autocomplete="url" required>
                                            <div class="form-text fs-11">{{ __('Tautan lengkap (http/https) yang akan dibuka peserta.') }}</div>
                                        </div>
                                    @elseif ($lesson->normalizedType() === 'penugasan')
                                        @php
                                            $penugasanMaxMb = round(((int) config('mooc.penugasan_max_kb', 10240)) / 1024, 1);
                                        @endphp
                                        <div class="col-12">
                                            <label class="form-label fs-12">{{ __('Berkas instruksi') }}</label>
                                            @if ($lesson->file_url)
                                                <div class="mb-1 fs-12">
                                                    <a href="{{ $lesson->externalUrl() }}" target="_blank" rel="noopener">
                                                        <i class="ri-attachment-2 me-1"></i>{{ __('Lihat berkas saat ini') }}
                                                    </a>
                                                </div>
                                            @endif
                                            <input type="file" name="berkas_file" class="form-control form-control-sm js-upload-size"
                                                accept=".pdf,.doc,.docx,.ppt,.pptx,.zip"
                                                data-max-kb="{{ (int) config('mooc.penugasan_max_kb', 10240) }}"
                                                data-label="{{ __('Berkas instruksi') }}"
                                                @required(! $lesson->file_url)>
                                            <div class="form-text fs-11">
                                                {{ __('Kosongkan jika tidak diganti. Word, PPT, PDF, ZIP — maks. :max MB.', ['max' => $penugasanMaxMb]) }}
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $berkasMaxMb = round(((int) config('mooc.berkas_max_kb', 10240)) / 1024, 1);
                                        @endphp
                                        <div class="col-12">
                                            <label class="form-label fs-12">{{ __('Unggah berkas') }}</label>
                                            @if ($lesson->file_url)
                                                <div class="mb-1 fs-12">
                                                    <a href="{{ $lesson->externalUrl() }}" target="_blank" rel="noopener">
                                                        <i class="ri-attachment-2 me-1"></i>{{ __('Lihat berkas saat ini') }}
                                                    </a>
                                                </div>
                                            @endif
                                            <input type="file" name="berkas_file" class="form-control form-control-sm js-upload-size"
                                                accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.txt,.png,.jpg,.jpeg,.webp"
                                                data-max-kb="{{ (int) config('mooc.berkas_max_kb', 10240) }}"
                                                data-label="{{ __('Berkas') }}"
                                                @required(! $lesson->file_url)>
                                            <div class="form-text fs-11">
                                                {{ __('Kosongkan jika tidak diganti. Maks. :max MB.', ['max' => $berkasMaxMb]) }}
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-12 d-flex gap-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="is_preview" value="1" class="form-check-input" @checked($lesson->is_preview)>
                                            <label class="form-check-label fs-12">{{ __('Preview') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="hidden" name="is_required" value="0">
                                            <input type="checkbox" name="is_required" value="1" class="form-check-input" @checked($lesson->is_required)>
                                            <label class="form-check-label fs-12">{{ __('Wajib') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-sm btn-primary btn-wave">{{ __('Simpan aktivitas') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endcan
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
            <button type="button" class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#topicCreateModal">
                <i class="ri-add-line me-1"></i>{{ __('Tambah topik') }}
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
                        @foreach (ActivityTypes::palette() as $item)
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
            <div class="modal-content">
                <form method="POST" id="activityCreateForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tipe" id="activityFormType">
                    <input type="hidden" name="is_required" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Tambah') }}: <span id="activityFormTypeLabel"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6 class="text-muted text-uppercase fs-11 mb-3">{{ __('Umum') }}</h6>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Nama') }} <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Deskripsi') }}</label>
                            <textarea name="body" class="form-control" rows="4" placeholder="{{ __('Deskripsi aktivitas...') }}"></textarea>
                        </div>
                        <div class="mb-3 d-none" id="activityFieldVideo">
                            @php
                                $videoMaxMb = round(((int) config('mooc.video_max_kb', 51200)) / 1024, 1);
                                $videoMimes = implode(', ', (array) config('mooc.video_mimes', ['mp4', 'webm']));
                            @endphp
                            <label class="form-label">{{ __('Unggah video') }} <span class="text-danger">*</span></label>
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
                        @endphp
                        <div class="mb-3 d-none" id="activityFieldBerkas"
                            data-berkas-max-kb="{{ (int) config('mooc.berkas_max_kb', 10240) }}"
                            data-penugasan-max-kb="{{ (int) config('mooc.penugasan_max_kb', 10240) }}"
                            data-berkas-help="{{ __('Maksimal :max MB. Format: :formats', ['max' => $berkasMaxMb, 'formats' => $berkasMimes]) }}">
                            <label class="form-label">{{ __('Unggah berkas') }} <span class="text-danger">*</span></label>
                            <input type="file" name="berkas_file" class="form-control js-upload-size"
                                accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.txt,.png,.jpg,.jpeg,.webp"
                                data-max-kb="{{ (int) config('mooc.berkas_max_kb', 10240) }}"
                                data-label="{{ __('Berkas') }}">
                            <div class="form-text">
                                {{ __('Maksimal :max MB. Format: :formats', ['max' => $berkasMaxMb, 'formats' => $berkasMimes]) }}
                            </div>
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
@endcan
