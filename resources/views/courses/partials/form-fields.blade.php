@php
    $course = $course ?? null;
    $startsAt = old('starts_at', optional($course?->starts_at)->format('Y-m-d\TH:i'));
    $endsAt = old('ends_at', optional($course?->ends_at)->format('Y-m-d\TH:i'));
    $currentThumb = $course?->thumbnail_url;
    $learningCategories = \App\Models\LearningCategory::query()->orderBy('name')->get(['id', 'name']);
    $learningTags = \App\Models\LearningTag::query()->orderBy('name')->get(['id', 'name']);
    $selectedTagIds = collect(old('tag_ids', $course?->relationLoaded('tags')
        ? $course->tags->pluck('id')->all()
        : ($course?->tags()->pluck('learning_tags.id')->all() ?? [])
    ))->map(fn ($id) => (string) $id)->all();
    $selectedKategori = old('kategori', $course?->kategori);
    $selectedInstansi = old('instansi', $course?->instansi ?? 'Internal');
@endphp
<div class="course-form-moodle">
    <div class="course-form-section">
        <h3 class="course-form-section__title">{{ __('Umum') }}</h3>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">{{ __('Nama kursus') }} <span class="text-danger">*</span></label>
                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                    value="{{ old('judul', $course?->judul) }}" required maxlength="255"
                    placeholder="{{ __('Contoh: Audit Intern dan Perkembangan Teknologi Informasi') }}">
                @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label d-block mb-2">{{ __('Instansi') }} <span class="text-danger">*</span></label>
                <div class="course-form-radio-group">
                    <div class="form-check course-form-radio">
                        <input
                            class="form-check-input @error('instansi') is-invalid @enderror"
                            type="radio"
                            name="instansi"
                            id="instansi-internal-{{ $course?->id ?? 'new' }}"
                            value="Internal"
                            @checked($selectedInstansi === 'Internal')
                        >
                        <label class="form-check-label" for="instansi-internal-{{ $course?->id ?? 'new' }}">
                            {{ __('Internal') }}
                        </label>
                    </div>
                    <div class="form-check course-form-radio">
                        <input
                            class="form-check-input @error('instansi') is-invalid @enderror"
                            type="radio"
                            name="instansi"
                            id="instansi-eksternal-{{ $course?->id ?? 'new' }}"
                            value="Eksternal"
                            @checked($selectedInstansi === 'Eksternal')
                        >
                        <label class="form-check-label" for="instansi-eksternal-{{ $course?->id ?? 'new' }}">
                            {{ __('Eksternal') }}
                        </label>
                    </div>
                </div>
                @error('instansi')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('Kategori') }} <span class="text-danger">*</span></label>
                <select
                    name="kategori"
                    class="form-select course-form-native-select @error('kategori') is-invalid @enderror"
                    data-searchable-select
                    data-placeholder="{{ __('Pilih kategori...') }}"
                    data-search-placeholder="{{ __('Cari kategori') }}"
                    required
                >
                    <option value="">{{ __('Pilih kategori...') }}</option>
                    @foreach ($learningCategories as $category)
                        <option value="{{ $category->name }}" @selected($selectedKategori === $category->name)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('kategori')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('Nomor ID kursus') }}</label>
                <input type="text" name="id_number" class="form-control @error('id_number') is-invalid @enderror"
                    value="{{ old('id_number', $course?->id_number) }}" maxlength="100">
                @error('id_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('Tag') }}</label>
                <select
                    name="tag_ids[]"
                    id="courseTagSelect-{{ $course?->id ?? 'new' }}"
                    class="form-select course-form-native-select course-form-native-select--multi @error('tag_ids') is-invalid @enderror"
                    data-searchable-select
                    data-search-placeholder="{{ __('Cari tag') }}"
                    data-placeholder="{{ __('Pilih satu atau lebih tag...') }}"
                    multiple>
                    @foreach ($learningTags as $tag)
                        <option value="{{ $tag->id }}" @selected(in_array((string) $tag->id, $selectedTagIds, true))>
                            {{ $tag->name }}
                        </option>
                    @endforeach
                </select>
                @error('tag_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('tag_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('Tanggal mulai') }}</label>
                <input type="datetime-local" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror"
                    value="{{ $startsAt }}">
                @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label d-flex align-items-center justify-content-between gap-2">
                    <span>{{ __('Tanggal selesai') }}</span>
                    <span class="form-check mb-0">
                        <input type="hidden" name="ends_at_enabled" value="0">
                        <input type="checkbox" name="ends_at_enabled" value="1" class="form-check-input"
                            id="endsAtEnabled-{{ $course?->id ?? 'new' }}" @checked(old('ends_at_enabled', $course?->ends_at_enabled))
                            onchange="document.getElementById('endsAtInput-{{ $course?->id ?? 'new' }}').disabled = !this.checked">
                        <label class="form-check-label" for="endsAtEnabled-{{ $course?->id ?? 'new' }}">{{ __('Aktifkan') }}</label>
                    </span>
                </label>
                <input type="datetime-local" name="ends_at" id="endsAtInput-{{ $course?->id ?? 'new' }}"
                    class="form-control @error('ends_at') is-invalid @enderror"
                    value="{{ $endsAt }}"
                    @disabled(! old('ends_at_enabled', $course?->ends_at_enabled))>
                @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="course-form-section mt-4">
        <h3 class="course-form-section__title">{{ __('Deskripsi') }}</h3>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">{{ __('Ringkasan kursus') }}</label>
                <textarea name="deskripsi" rows="5" class="form-control @error('deskripsi') is-invalid @enderror"
                    placeholder="{{ __('Ringkasan singkat tentang kursus...') }}">{{ old('deskripsi', $course?->deskripsi) }}</textarea>
                @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="course-form-section mt-4">
        <h3 class="course-form-section__title">{{ __('Gambar kursus') }}</h3>
        <div class="row g-3 align-items-start">
            <div class="col-md-4">
                <div class="course-thumb-preview">
                    <img
                        src="{{ $currentThumb }}"
                        alt="{{ __('Pratinjau thumbnail') }}"
                        id="courseThumbPreview-{{ $course?->id ?? 'new' }}"
                        class="course-thumb-preview__img"
                        onerror="this.onerror=null;this.src='{{ asset(config('mooc.course_placeholder', 'images/course-no-image.png')) }}';"
                    >
                </div>
            </div>
            <div class="col-md-8">
                <label class="form-label">{{ __('Thumbnail') }}</label>
                <input type="file" name="thumbnail_file" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif"
                    class="form-control @error('thumbnail_file') is-invalid @enderror"
                    data-thumb-preview="courseThumbPreview-{{ $course?->id ?? 'new' }}">
                <div class="form-text">{{ __('Unggah gambar JPG/PNG/WebP (maks. 2 MB). Kosongkan untuk tetap memakai gambar saat ini / default.') }}</div>
                @error('thumbnail_file')<div class="invalid-feedback">{{ $message }}</div>@enderror

                @if ($course?->hasUsableThumbnail())
                    <div class="form-check mt-2">
                        <input type="checkbox" name="remove_thumbnail" value="1" class="form-check-input" id="removeThumb-{{ $course->id }}">
                        <label class="form-check-label" for="removeThumb-{{ $course->id }}">{{ __('Hapus thumbnail (pakai default)') }}</label>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="course-form-section mt-4">
        <div class="form-check form-switch">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" class="form-check-input"
                id="isPublished-{{ $course?->id ?? 'new' }}"
                role="switch"
                @checked(old('is_published', $course?->is_published ?? true))>
            <label class="form-check-label" for="isPublished-{{ $course?->id ?? 'new' }}">
                {{ __('Publikasikan ke katalog peserta') }}
            </label>
        </div>
        <div class="form-text">{{ __('Jika aktif, kursus tampil di katalog dan bisa didaftarkan peserta.') }}</div>
    </div>
</div>
