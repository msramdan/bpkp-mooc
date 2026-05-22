@php
    $course = $course ?? null;
@endphp
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">{{ __('Kode') }}</label>
        <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
            value="{{ old('kode', $course?->kode) }}" required maxlength="20">
        @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label class="form-label">{{ __('Judul') }}</label>
        <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
            value="{{ old('judul', $course?->judul) }}" required>
        @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('Kategori') }}</label>
        <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror"
            value="{{ old('kategori', $course?->kategori) }}" required>
        @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('Instruktur') }}</label>
        <input type="text" name="instruktur" class="form-control @error('instruktur') is-invalid @enderror"
            value="{{ old('instruktur', $course?->instruktur) }}" required>
        @error('instruktur')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('URL thumbnail') }}</label>
        <input type="url" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror"
            value="{{ old('thumbnail', $course?->thumbnail) }}" required>
        @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('Durasi (jam)') }}</label>
        <input type="number" name="durasi_jam" class="form-control @error('durasi_jam') is-invalid @enderror"
            value="{{ old('durasi_jam', $course?->durasi_jam ?? 12) }}" min="1" required>
        @error('durasi_jam')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('Jumlah modul') }}</label>
        <input type="number" name="modul_total" class="form-control @error('modul_total') is-invalid @enderror"
            value="{{ old('modul_total', $course?->modul_total ?? 6) }}" min="1" max="30" required>
        @error('modul_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('Level') }}</label>
        <select name="level" class="form-select @error('level') is-invalid @enderror" required>
            @foreach (['Pemula', 'Menengah', 'Lanjutan'] as $level)
                <option value="{{ $level }}" @selected(old('level', $course?->level) === $level)>{{ $level }}</option>
            @endforeach
        </select>
        @error('level')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('Rating') }}</label>
        <input type="number" step="0.1" name="rating" class="form-control @error('rating') is-invalid @enderror"
            value="{{ old('rating', $course?->rating ?? 0) }}" min="0" max="5">
        @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('Deskripsi') }}</label>
        <textarea name="deskripsi" rows="3" class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi', $course?->deskripsi) }}</textarea>
        @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" class="form-check-input" id="isPublished"
                @checked(old('is_published', $course?->is_published ?? true))>
            <label class="form-check-label" for="isPublished">{{ __('Publikasikan ke katalog peserta') }}</label>
        </div>
    </div>
</div>
