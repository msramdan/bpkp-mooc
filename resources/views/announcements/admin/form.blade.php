@extends('layouts.app')

@php
    /** @var \App\Models\Announcement $announcement */
    $isEdit = $announcement->exists;
    $tz = \App\Http\Requests\AnnouncementRequest::TZ;
    $dt = fn ($d) => $d?->timezone($tz)->format('Y-m-d H:i');
    $val = fn ($key, $default = null) => old($key, $default);
    $category = $val('category', $announcement->category);
    $audience = $val('audience', $announcement->audience);
    $mode = $val('display_mode', $announcement->display_mode);
    $active = (bool) $val('is_active', $announcement->is_active);
    $catHints = ['info' => __('Informasi umum'), 'penting' => __('Perlu diperhatikan'), 'mendesak' => __('Segera ditindaklanjuti')];
    $audHints = ['peserta' => __('Semua akun peserta'), 'all' => __('Peserta & admin'), 'admin' => __('Hanya admin')];
    $modeHints = ['once' => __('Tidak muncul lagi setelah ditutup'), 'every_login' => __('Muncul lagi tiap login')];
@endphp

@section('title', $isEdit ? __('Ubah Pengumuman') : __('Buat Pengumuman'))

@push('css')
    <style>
        .ann-preview-frame .ann-body:empty::before { content: attr(data-empty); color: var(--ann-muted); font-style: italic; }
    </style>
@endpush

@section('content')
    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ $isEdit ? __('Ubah Pengumuman') : __('Buat Pengumuman') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">{{ __('Pengumuman') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? __('Ubah') : __('Buat') }}</li>
            </ol>
        </div>
        @if ($isEdit)
            <span class="ann-status ann-status--{{ $announcement->status() }}">{{ \App\Http\Controllers\AnnouncementController::statusLabel($announcement->status()) }}</span>
        @endif
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex gap-2" role="alert">
            <i class="ri-error-warning-line fs-18"></i>
            <div>
                <strong>{{ __('Periksa kembali isian berikut:') }}</strong>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" id="annForm" novalidate
          action="{{ $isEdit ? route('announcements.update', $announcement) : route('announcements.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-xl-7">
                {{-- 1. Isi --}}
                <section class="ann-form-section">
                    <h3><i class="ri-file-text-line"></i>{{ __('Isi pengumuman') }}</h3>
                    <p>{{ __('Judul wajib diisi. Isi boleh dikosongkan bila cukup judul + lampiran/tautan. Tautan http(s) di dalam isi otomatis bisa diklik.') }}</p>

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">{{ __('Judul') }} <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" maxlength="150" required class="form-control @error('title') is-invalid @enderror"
                               value="{{ $val('title', $announcement->title) }}" placeholder="{{ __('mis. Jadwal Ujian Akhir Diklat Batch 3') }}" data-ann-count="150">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="body" class="form-label fw-semibold">{{ __('Deskripsi / informasi') }} <span class="text-muted fw-normal">({{ __('opsional') }})</span></label>
                        <textarea id="body" name="body" rows="7" maxlength="5000" class="form-control @error('body') is-invalid @enderror"
                                  placeholder="{{ __('Tulis isi pengumuman. Tekan Enter untuk baris baru.') }}" data-ann-count="5000">{{ $val('body', $announcement->body) }}</textarea>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <label class="form-label fw-semibold d-block">{{ __('Kategori') }}</label>
                    <div class="ann-choice">
                        @foreach (\App\Models\Announcement::CATEGORIES as $key => $label)
                            <label class="ann-cat--{{ $key }}">
                                <input type="radio" name="category" value="{{ $key }}" {{ $category === $key ? 'checked' : '' }}>
                                <span class="ann-chip align-self-start"><span class="ann-dot"></span>{{ $label }}</span>
                                <span class="mt-1">{{ $catHints[$key] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('category')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
                </section>

                {{-- 2. Penayangan --}}
                <section class="ann-form-section">
                    <h3><i class="ri-calendar-schedule-line"></i>{{ __('Penayangan') }}</h3>
                    <p>{{ __('Atur kapan pengumuman tayang otomatis. Kosongkan tanggal mulai untuk langsung tayang, kosongkan tanggal berakhir untuk tanpa batas. Semua waktu dalam WIB.') }}</p>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 rounded-3 mb-3" style="background: var(--ann-soft)">
                        <div>
                            <div class="fw-semibold">{{ __('Aktifkan pengumuman') }}</div>
                            <div class="fs-12 text-muted">{{ __('Bila dimatikan, pengumuman tidak tampil walau masih dalam jadwal.') }}</div>
                        </div>
                        <label class="ann-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ $active ? 'checked' : '' }} id="is_active">
                            <span class="ann-switch__track"></span>
                            <span data-ann-active-label>{{ $active ? __('Aktif') : __('Mati') }}</span>
                        </label>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="starts_at" class="form-label fw-semibold">{{ __('Mulai tayang') }}</label>
                            <input type="text" id="starts_at" name="starts_at" autocomplete="off" class="form-control @error('starts_at') is-invalid @enderror"
                                   value="{{ $val('starts_at', $dt($announcement->starts_at)) }}" placeholder="{{ __('Langsung tayang') }}" data-ann-date>
                            @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label for="ends_at" class="form-label fw-semibold">{{ __('Berakhir') }}</label>
                            <input type="text" id="ends_at" name="ends_at" autocomplete="off" class="form-control @error('ends_at') is-invalid @enderror"
                                   value="{{ $val('ends_at', $dt($announcement->ends_at)) }}" placeholder="{{ __('Tanpa batas') }}" data-ann-date>
                            @error('ends_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="d-flex flex-wrap gap-1 mt-2" aria-label="{{ __('Pilihan cepat durasi') }}">
                                @foreach ([1 => __('+1 hari'), 7 => __('+7 hari'), 30 => __('+30 hari')] as $days => $label)
                                    <button type="button" class="btn btn-sm btn-light" data-ann-quick="{{ $days }}">{{ $label }}</button>
                                @endforeach
                                <button type="button" class="btn btn-sm btn-light" data-ann-quick="0">{{ __('Tanpa batas') }}</button>
                            </div>
                        </div>
                    </div>

                    <label class="form-label fw-semibold d-block mt-4">{{ __('Ditujukan untuk') }}</label>
                    <div class="ann-choice">
                        @foreach (\App\Models\Announcement::AUDIENCES as $key => $label)
                            <label>
                                <input type="radio" name="audience" value="{{ $key }}" {{ $audience === $key ? 'checked' : '' }}>
                                <strong>{{ $label }}</strong>
                                <span>{{ $audHints[$key] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <label class="form-label fw-semibold d-block mt-4">{{ __('Frekuensi pop up') }}</label>
                    <div class="ann-choice" style="grid-template-columns: repeat(2, minmax(0, 1fr))">
                        @foreach (['once' => __('Sekali per pengguna'), 'every_login' => __('Setiap kali login')] as $key => $label)
                            <label>
                                <input type="radio" name="display_mode" value="{{ $key }}" {{ $mode === $key ? 'checked' : '' }}>
                                <strong>{{ $label }}</strong>
                                <span>{{ $modeHints[$key] }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- 3. Tautan & lampiran --}}
                <section class="ann-form-section">
                    <h3><i class="ri-attachment-2"></i>{{ __('Tautan & lampiran') }} <span class="text-muted fw-normal fs-12">({{ __('opsional') }})</span></h3>
                    <p>{{ __('Tambahkan tombol tautan (mis. Zoom, Google Form) dan/atau satu berkas lampiran.') }}</p>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="link_url" class="form-label fw-semibold">{{ __('URL tautan') }}</label>
                            <input type="url" id="link_url" name="link_url" maxlength="2048" inputmode="url" class="form-control @error('link_url') is-invalid @enderror"
                                   value="{{ $val('link_url', $announcement->link_url) }}" placeholder="https://">
                            @error('link_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="link_label" class="form-label fw-semibold">{{ __('Teks tombol') }}</label>
                            <input type="text" id="link_label" name="link_label" maxlength="60" class="form-control @error('link_label') is-invalid @enderror"
                                   value="{{ $val('link_label', $announcement->link_label) }}" placeholder="{{ __('Buka tautan') }}">
                            @error('link_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="attachment" class="form-label fw-semibold">{{ __('Lampiran') }}</label>
                        @if ($isEdit && $announcement->hasAttachment())
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2" data-ann-current-file>
                                <a class="ann-file" style="flex: 0 1 360px" href="{{ route('announcements.attachment', $announcement) }}">
                                    <span class="ann-file__ext">{{ $announcement->attachmentExtension() }}</span>
                                    <span class="ann-file__info">
                                        <span class="ann-file__name">{{ $announcement->attachment_name }}</span>
                                        <span class="ann-file__size">{{ $announcement->attachmentSizeLabel() }}</span>
                                    </span>
                                </a>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_attachment" value="1" id="remove_attachment" {{ old('remove_attachment') ? 'checked' : '' }}>
                                    <label class="form-check-label text-danger" for="remove_attachment">{{ __('Hapus lampiran ini') }}</label>
                                </div>
                            </div>
                        @endif
                        <input type="file" id="attachment" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                               accept="{{ collect(\App\Models\Announcement::ATTACHMENT_MIMES)->map(fn ($m) => '.'.$m)->implode(',') }}">
                        <div class="form-text">{{ __('PDF, Word, Excel, PowerPoint, gambar (JPG/PNG/WEBP) atau ZIP. Maks :mb MB.', ['mb' => \App\Models\Announcement::ATTACHMENT_MAX_KB / 1024]) }}
                            @if ($isEdit && $announcement->hasAttachment()) {{ __('Unggah berkas baru untuk mengganti.') }} @endif
                        </div>
                        @error('attachment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </section>

                <div class="d-flex flex-wrap gap-2 justify-content-end mb-4">
                    <a href="{{ route('announcements.index') }}" class="btn btn-light">{{ __('Batal') }}</a>
                    <button type="submit" class="btn btn-primary btn-wave" data-ann-submit>
                        <i class="ri-save-3-line me-1"></i>{{ $isEdit ? __('Simpan perubahan') : __('Terbitkan pengumuman') }}
                    </button>
                </div>
            </div>

            {{-- Pratinjau langsung --}}
            <div class="col-xl-5">
                <div class="ann-sticky">
                    <div class="ann-preview-label"><i class="ri-eye-line me-1"></i>{{ __('Pratinjau pop up') }}</div>
                    <div class="ann-preview-frame ann-modal">
                        <div class="ann-modal__head">
                            <div class="ann-modal__eyebrow">
                                <span class="ann-modal__horn"><i class="ri-megaphone-fill" aria-hidden="true"></i></span>
                                <span>{{ __('Pengumuman') }}</span>
                            </div>
                        </div>
                        <article class="ann-slide is-active ann-cat--{{ $category }}" data-pv-slide>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="ann-chip"><span class="ann-dot"></span><span data-pv-cat>{{ \App\Models\Announcement::CATEGORIES[$category] ?? '' }}</span></span>
                                <span class="ann-meta" data-pv-until></span>
                            </div>
                            <h2 class="ann-slide__title" data-pv-title></h2>
                            <div class="ann-body" data-pv-body data-empty="{{ __('Isi pengumuman akan tampil di sini.') }}"></div>
                            <div class="ann-actions" data-pv-actions></div>
                            <div class="pb-3"></div>
                        </article>
                    </div>
                    @if ($isEdit)
                        <div class="ann-meta mt-3">
                            <span><i class="ri-eye-line"></i>{{ __(':n pengguna sudah melihat', ['n' => number_format($announcement->seen_count ?? 0, 0, ',', '.')]) }}</span>
                            <span><i class="ri-checkbox-circle-line"></i>{{ __(':n menutup pop up', ['n' => number_format($announcement->dismissed_count ?? 0, 0, ',', '.')]) }}</span>
                            @if ($announcement->creator)
                                <span><i class="ri-user-line"></i>{{ __('Dibuat oleh :n', ['n' => $announcement->creator->name]) }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.getElementById('annForm');
            var cats = @json(\App\Models\Announcement::CATEGORIES);
            var t = { untitled: @json(__('Judul pengumuman')), until: @json(__('Berlaku s.d.')), open: @json(__('Buka tautan')), file: @json(__('Lampiran')) };
            var $ = function (s) { return form.querySelector(s); };

            // Penghitung karakter
            form.querySelectorAll('[data-ann-count]').forEach(function (el) {
                var c = document.createElement('div');
                c.className = 'ann-counter';
                el.insertAdjacentElement('afterend', c);
                var upd = function () { c.textContent = el.value.length + ' / ' + el.dataset.annCount; };
                el.addEventListener('input', upd); upd();
            });

            // Tanggal (WIB)
            var pickers = {};
            if (window.flatpickr) {
                form.querySelectorAll('[data-ann-date]').forEach(function (el) {
                    pickers[el.id] = flatpickr(el, { enableTime: true, time_24hr: true, dateFormat: 'Y-m-d H:i', allowInput: true, minuteIncrement: 5, onChange: render });
                });
            }
            var pad = function (n) { return (n < 10 ? '0' : '') + n; };
            form.querySelectorAll('[data-ann-quick]').forEach(function (b) {
                b.addEventListener('click', function () {
                    var days = parseInt(b.dataset.annQuick, 10);
                    var end = $('#ends_at');
                    if (!days) { if (pickers.ends_at) pickers.ends_at.clear(); end.value = ''; render(); return; }
                    var startVal = $('#starts_at').value;
                    var base = startVal ? new Date(startVal.replace(' ', 'T')) : new Date();
                    if (isNaN(base)) base = new Date();
                    base.setDate(base.getDate() + days);
                    var v = base.getFullYear() + '-' + pad(base.getMonth() + 1) + '-' + pad(base.getDate()) + ' ' + pad(base.getHours()) + ':' + pad(Math.floor(base.getMinutes() / 5) * 5);
                    if (pickers.ends_at) pickers.ends_at.setDate(v, true); else end.value = v;
                    render();
                });
            });

            var active = $('#is_active');
            active.addEventListener('change', function () { form.querySelector('[data-ann-active-label]').textContent = active.checked ? @json(__('Aktif')) : @json(__('Mati')); });

            // Pratinjau langsung (pakai textContent: aman dari HTML)
            var slide = document.querySelector('[data-pv-slide]');
            function linkify(text, el) {
                el.textContent = '';
                var re = /https?:\/\/[^\s<>"']+/gi, last = 0, m;
                while ((m = re.exec(text))) {
                    var url = m[0].replace(/[.,);:!?]+$/, '');
                    el.appendChild(document.createTextNode(text.slice(last, m.index)));
                    var a = document.createElement('a'); a.href = url; a.textContent = url; a.target = '_blank'; a.rel = 'noopener noreferrer';
                    el.appendChild(a);
                    last = m.index + url.length;
                }
                el.appendChild(document.createTextNode(text.slice(last)));
                el.style.whiteSpace = 'pre-line';
            }
            function render() {
                var cat = (form.querySelector('input[name=category]:checked') || {}).value || 'info';
                slide.className = 'ann-slide is-active ann-cat--' + cat;
                document.querySelector('[data-pv-cat]').textContent = cats[cat] || cat;
                document.querySelector('[data-pv-title]').textContent = $('#title').value.trim() || t.untitled;
                linkify($('#body').value, document.querySelector('[data-pv-body]'));
                var until = document.querySelector('[data-pv-until]');
                until.textContent = $('#ends_at').value ? (t.until + ' ' + $('#ends_at').value + ' WIB') : '';

                var actions = document.querySelector('[data-pv-actions]');
                actions.textContent = '';
                var fileInput = $('#attachment'), remove = $('#remove_attachment'), current = form.querySelector('[data-ann-current-file] .ann-file__name');
                var fileName = fileInput.files && fileInput.files[0] ? fileInput.files[0].name : (current && !(remove && remove.checked) ? current.textContent : '');
                if (fileName) {
                    var f = document.createElement('span'); f.className = 'ann-file';
                    var ext = document.createElement('span'); ext.className = 'ann-file__ext'; ext.textContent = (fileName.split('.').pop() || 'FILE').toUpperCase().slice(0, 4);
                    var info = document.createElement('span'); info.className = 'ann-file__info';
                    var nm = document.createElement('span'); nm.className = 'ann-file__name'; nm.textContent = fileName;
                    var sz = document.createElement('span'); sz.className = 'ann-file__size'; sz.textContent = t.file;
                    info.appendChild(nm); info.appendChild(sz); f.appendChild(ext); f.appendChild(info); actions.appendChild(f);
                }
                var url = $('#link_url').value.trim();
                if (/^https?:\/\//i.test(url)) {
                    var b = document.createElement('span'); b.className = 'ann-link-btn';
                    b.textContent = ($('#link_label').value.trim() || t.open) + ' ';
                    var i = document.createElement('i'); i.className = 'ri-arrow-right-up-line'; b.appendChild(i);
                    actions.appendChild(b);
                }
            }
            form.addEventListener('input', render);
            form.addEventListener('change', render);
            render();

            // Cegah kirim ganda
            form.addEventListener('submit', function () {
                var btn = form.querySelector('[data-ann-submit]');
                setTimeout(function () { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + @json(__('Menyimpan...')); }, 0);
            });
        })();
    </script>
@endpush
