@can('course edit')
    <div class="card custom-card mb-3">
        <div class="card-header">
            <span class="card-title mb-0">{{ __('Tambah modul') }}</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('courses.modules.store', $course) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">{{ __('Judul modul') }}</label>
                    <input type="text" name="judul" class="form-control" required maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Deskripsi') }}</label>
                    <input type="text" name="deskripsi" class="form-control" maxlength="2000">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Durasi (menit)') }}</label>
                    <input type="number" name="durasi_menit" class="form-control" min="0" value="0">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-wave w-100">{{ __('Tambah') }}</button>
                </div>
            </form>
        </div>
    </div>
@endcan

@forelse ($course->modules as $module)
    <div class="card custom-card mb-3">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="card-title mb-0">
                {{ __('Modul') }} {{ $module->urutan }} — {{ $module->judul }}
            </span>
            @can('course edit')
                <form action="{{ route('courses.modules.destroy', [$course, $module]) }}" method="post" class="d-inline">
                    @csrf
                    @method('delete')
                    <button type="button" class="btn btn-sm btn-danger-light btn-wave js-delete-confirm">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </form>
            @endcan
        </div>
        <div class="card-body">
            @can('course edit')
                <form method="POST" action="{{ route('courses.modules.update', [$course, $module]) }}" class="row g-2 mb-3">
                    @csrf
                    @method('PUT')
                    <div class="col-md-4">
                        <input type="text" name="judul" class="form-control" value="{{ $module->judul }}" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="deskripsi" class="form-control" value="{{ $module->deskripsi }}" placeholder="{{ __('Deskripsi') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="durasi_menit" class="form-control" min="0" value="{{ $module->durasi_menit }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary btn-wave w-100">{{ __('Simpan') }}</button>
                    </div>
                </form>
            @else
                @if ($module->deskripsi)
                    <p class="text-muted mb-3">{{ $module->deskripsi }}</p>
                @endif
            @endcan

            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Judul materi') }}</th>
                            <th>{{ __('Tipe') }}</th>
                            <th>{{ __('Pratinjau') }}</th>
                            <th>{{ __('Wajib') }}</th>
                            @can('course edit')
                                <th class="text-end">{{ __('Aksi') }}</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($module->lessons as $lesson)
                            <tr>
                                <td>{{ $lesson->urutan }}</td>
                                <td>{{ $lesson->judul }}</td>
                                <td>{{ $lesson->typeLabel() }}</td>
                                <td>{{ $lesson->is_preview ? __('Ya') : __('Tidak') }}</td>
                                <td>{{ $lesson->is_required ? __('Ya') : __('Tidak') }}</td>
                                @can('course edit')
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-light btn-wave"
                                            data-bs-toggle="collapse" data-bs-target="#edit-lesson-{{ $lesson->id }}">
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
                                    </td>
                                @endcan
                            </tr>
                            @can('course edit')
                                <tr class="collapse" id="edit-lesson-{{ $lesson->id }}">
                                    <td colspan="6">
                                        <form method="POST" action="{{ route('courses.modules.lessons.update', [$course, $module, $lesson]) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="row g-2">
                                                <div class="col-md-3">
                                                    <input type="text" name="judul" class="form-control form-control-sm" value="{{ $lesson->judul }}" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <select name="tipe" class="form-select form-select-sm">
                                                        @foreach (\App\Models\CourseLesson::TYPES as $type)
                                                            <option value="{{ $type }}" @selected($lesson->tipe === $type)>{{ ucfirst($type) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="url" name="video_url" class="form-control form-control-sm" value="{{ $lesson->video_url }}" placeholder="Video URL">
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="url" name="file_url" class="form-control form-control-sm" value="{{ $lesson->file_url }}" placeholder="File URL">
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="is_preview" value="1" class="form-check-input" @checked($lesson->is_preview)>
                                                        <label class="form-check-label fs-12">{{ __('Preview') }}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="is_required" value="1" class="form-check-input" @checked($lesson->is_required)>
                                                        <label class="form-check-label fs-12">{{ __('Wajib') }}</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <textarea name="body" class="form-control form-control-sm" rows="2" placeholder="{{ __('Isi bacaan (HTML)') }}">{{ $lesson->body }}</textarea>
                                                </div>
                                                <div class="col-12 text-end">
                                                    <button type="submit" class="btn btn-sm btn-primary btn-wave">{{ __('Simpan materi') }}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endcan
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted text-center py-2">{{ __('Belum ada materi.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @can('course edit')
                <form method="POST" action="{{ route('courses.modules.lessons.store', [$course, $module]) }}" class="mt-3 border-top pt-3">
                    @csrf
                    <p class="fw-medium fs-13 mb-2">{{ __('Tambah materi') }}</p>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="judul" class="form-control form-control-sm" placeholder="{{ __('Judul materi') }}" required>
                        </div>
                        <div class="col-md-2">
                            <select name="tipe" class="form-select form-select-sm">
                                @foreach (\App\Models\CourseLesson::TYPES as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="url" name="video_url" class="form-control form-control-sm" placeholder="Video URL">
                        </div>
                        <div class="col-md-2">
                            <input type="url" name="file_url" class="form-control form-control-sm" placeholder="File URL">
                        </div>
                        <div class="col-md-1">
                            <div class="form-check">
                                <input type="checkbox" name="is_preview" value="1" class="form-check-input">
                                <label class="form-check-label fs-12">{{ __('Preview') }}</label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-check">
                                <input type="checkbox" name="is_required" value="1" class="form-check-input" checked>
                                <label class="form-check-label fs-12">{{ __('Wajib') }}</label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-sm btn-primary btn-wave w-100">+</button>
                        </div>
                        <div class="col-12">
                            <textarea name="body" class="form-control form-control-sm" rows="2" placeholder="{{ __('Isi bacaan (HTML, opsional)') }}"></textarea>
                        </div>
                    </div>
                </form>
            @endcan
        </div>
    </div>
@empty
    <div class="card custom-card">
        <div class="card-body text-center text-muted py-4">
            {{ __('Belum ada modul.') }}
        </div>
    </div>
@endforelse
