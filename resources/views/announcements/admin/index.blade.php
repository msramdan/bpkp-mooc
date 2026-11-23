@extends('layouts.app')

@section('title', __('Pengumuman'))

@section('content')
    @php
        $tz = \App\Http\Requests\AnnouncementRequest::TZ;
        $fmt = fn ($d) => $d?->timezone($tz)->translatedFormat('d M Y, H:i');
        $tabs = [
            'published' => __('Sedang tayang'),
            'scheduled' => __('Terjadwal'),
            'expired' => __('Berakhir'),
            'inactive' => __('Nonaktif'),
        ];
    @endphp

    <div class="my-4 page-header-breadcrumb d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-2">{{ __('Pengumuman') }}</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Pengumuman') }}</li>
            </ol>
        </div>
        <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-wave"><i class="ri-add-line me-1"></i>{{ __('Buat pengumuman') }}</a>
    </div>

    <div class="ann-admin-stats">
        @foreach ($tabs as $key => $label)
            <a href="{{ $status === $key ? route('announcements.index', array_filter(['q' => $q])) : route('announcements.index', array_filter(['status' => $key, 'q' => $q])) }}"
               class="ann-stat ann-stat--{{ $key }} {{ $status === $key ? 'is-active' : '' }}">
                <strong data-ann-stat="{{ $key }}">{{ number_format($stats[$key], 0, ',', '.') }}</strong>
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </div>

    <div class="ann-toolbar">
        <form method="GET" role="search">
            @if ($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="input-group">
                <span class="input-group-text bg-transparent"><i class="ri-search-line"></i></span>
                <input type="search" name="q" value="{{ $q }}" maxlength="100" class="form-control" placeholder="{{ __('Cari judul atau isi pengumuman') }}" aria-label="{{ __('Cari pengumuman') }}">
            </div>
        </form>
        @if ($status || $q !== '')
            <a href="{{ route('announcements.index') }}" class="btn btn-light"><i class="ri-close-line me-1"></i>{{ __('Reset filter') }}</a>
        @endif
    </div>

    @forelse ($announcements as $a)
        @php $st = $a->status(); @endphp
        <div class="ann-row ann-cat--{{ $a->category }} {{ $a->is_active ? '' : 'is-off' }}" data-ann-row="{{ $a->id }}">
            <div class="ann-row__strip"></div>
            <div class="ann-row__main">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="ann-chip"><span class="ann-dot"></span>{{ \App\Models\Announcement::CATEGORIES[$a->category] ?? $a->category }}</span>
                    <span class="ann-status ann-status--{{ $st }}" data-ann-status>{{ \App\Http\Controllers\AnnouncementController::statusLabel($st) }}</span>
                </div>
                <h2 class="ann-row__title">{{ $a->title }}</h2>
                @if (filled($a->body))
                    <p class="ann-row__excerpt">{{ \Illuminate\Support\Str::limit($a->body, 220) }}</p>
                @endif
                <div class="ann-meta">
                    <span><i class="ri-calendar-event-line"></i>{{ $a->starts_at ? $fmt($a->starts_at) : __('Langsung tayang') }} &rarr; {{ $a->ends_at ? $fmt($a->ends_at) : __('tanpa batas') }}</span>
                    <span><i class="ri-group-line"></i>{{ \App\Models\Announcement::AUDIENCES[$a->audience] ?? $a->audience }}</span>
                    <span><i class="ri-repeat-line"></i>{{ $a->display_mode === 'every_login' ? __('Setiap login') : __('Sekali') }}</span>
                    @if ($a->hasAttachment())
                        <span><i class="ri-attachment-2"></i>{{ $a->attachmentExtension() }}</span>
                    @endif
                    @if (filled($a->link_url))
                        <span><i class="ri-link"></i>{{ __('Tautan') }}</span>
                    @endif
                    <span title="{{ __('Jumlah pengguna yang sudah melihat / menutup pop up') }}"><i class="ri-eye-line"></i>{{ __(':s dilihat · :d ditutup', ['s' => number_format($a->seen_count, 0, ',', '.'), 'd' => number_format($a->dismissed_count, 0, ',', '.')]) }}</span>
                </div>
            </div>
            <div class="ann-row__side">
                <label class="ann-switch" title="{{ __('Nyalakan / matikan') }}">
                    <input type="checkbox" data-ann-toggle="{{ route('announcements.toggle', $a) }}" {{ $a->is_active ? 'checked' : '' }}
                           aria-label="{{ __('Status aktif :t', ['t' => $a->title]) }}">
                    <span class="ann-switch__track"></span>
                    <span data-ann-switch-label>{{ $a->is_active ? __('Aktif') : __('Mati') }}</span>
                </label>
                <div class="ann-row__btns">
                    <button type="button" class="btn btn-sm btn-light" data-ann-preview="{{ $a->id }}" title="{{ __('Pratinjau') }}" aria-label="{{ __('Pratinjau') }}"><i class="ri-eye-line"></i></button>
                    <a href="{{ route('announcements.edit', $a) }}" class="btn btn-sm btn-primary-light" title="{{ __('Ubah') }}" aria-label="{{ __('Ubah') }}"><i class="ri-pencil-line"></i></a>
                    <form method="POST" action="{{ route('announcements.destroy', $a) }}" class="js-ann-delete" data-title="{{ $a->title }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger-light" title="{{ __('Hapus') }}" aria-label="{{ __('Hapus') }}"><i class="ri-delete-bin-line"></i></button>
                    </form>
                </div>
            </div>
            <template data-ann-template="{{ $a->id }}">
                <article class="ann-slide is-active ann-cat--{{ $a->category }}">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="ann-chip"><span class="ann-dot"></span>{{ \App\Models\Announcement::CATEGORIES[$a->category] ?? $a->category }}</span>
                        @if ($a->ends_at)
                            <span class="ann-meta"><span><i class="ri-timer-line"></i>{{ __('Berlaku s.d. :d', ['d' => $fmt($a->ends_at)]) }} WIB</span></span>
                        @endif
                    </div>
                    <h2 class="ann-slide__title">{{ $a->title }}</h2>
                    @include('announcements.partials.content', ['a' => $a])
                    <div class="pb-3"></div>
                </article>
            </template>
        </div>
    @empty
        <div class="ann-empty">
            <i class="ri-megaphone-line" aria-hidden="true"></i>
            <h6 class="fw-semibold mb-1">{{ $status || $q !== '' ? __('Tidak ada pengumuman yang cocok') : __('Belum ada pengumuman') }}</h6>
            <p class="mb-3 fs-13">{{ __('Buat pengumuman untuk ditampilkan sebagai pop up setelah peserta login.') }}</p>
            <a href="{{ route('announcements.create') }}" class="btn btn-primary"><i class="ri-add-line me-1"></i>{{ __('Buat pengumuman') }}</a>
        </div>
    @endforelse

    <div class="mt-3">{{ $announcements->links() }}</div>

    {{-- Pratinjau: tampilan sama persis dengan pop up yang dilihat peserta --}}
    <div class="modal fade ann-modal" id="annPreview" tabindex="-1" aria-labelledby="annPreviewTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="ann-modal__head">
                    <div class="ann-modal__eyebrow">
                        <span class="ann-modal__horn"><i class="ri-megaphone-fill" aria-hidden="true"></i></span>
                        <span id="annPreviewTitle">{{ __('Pratinjau pengumuman') }}</span>
                    </div>
                    <button type="button" class="ann-modal__close" data-bs-dismiss="modal" aria-label="{{ __('Tutup') }}"><i class="ri-close-line"></i></button>
                </div>
                <div class="ann-modal__body" data-ann-preview-body></div>
                <div class="ann-modal__foot">
                    <span class="fs-12 text-muted">{{ __('Seperti inilah pop up yang dilihat pengguna.') }}</span>
                    <div class="ann-modal__nav"><button type="button" class="ann-btn-primary" data-bs-dismiss="modal">{{ __('Tutup') }}</button></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var token = document.querySelector('meta[name=csrf-token]').content;

            // Saklar on/off (AJAX). Gagal -> kembalikan posisi saklar.
            document.querySelectorAll('[data-ann-toggle]').forEach(function (input) {
                input.addEventListener('change', function () {
                    var label = input.closest('.ann-switch');
                    var row = input.closest('[data-ann-row]');
                    var want = input.checked;
                    label.classList.add('is-busy');
                    fetch(input.dataset.annToggle, {
                        method: 'PATCH', credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ is_active: want })
                    }).then(function (r) { if (!r.ok) throw r; return r.json(); })
                      .then(function (res) {
                          row.classList.toggle('is-off', !res.is_active);
                          label.querySelector('[data-ann-switch-label]').textContent = res.is_active ? @json(__('Aktif')) : @json(__('Mati'));
                          var st = row.querySelector('[data-ann-status]');
                          st.className = 'ann-status ann-status--' + res.status;
                          st.textContent = res.status_label;
                          Object.keys(res.stats || {}).forEach(function (k) {
                              var el = document.querySelector('[data-ann-stat="' + k + '"]');
                              if (el) el.textContent = Number(res.stats[k]).toLocaleString('id-ID');
                          });
                          if (window.BpkpBootstrapToast) BpkpBootstrapToast.show(res.message, 'success');
                      })
                      .catch(function () {
                          input.checked = !want;
                          if (window.Swal) Swal.fire({ icon: 'error', title: @json(__('Gagal mengubah status')), text: @json(__('Coba muat ulang halaman lalu ulangi.')) });
                      })
                      .finally(function () { label.classList.remove('is-busy'); });
                });
            });

            // Pratinjau
            var previewEl = document.getElementById('annPreview');
            var previewBody = previewEl.querySelector('[data-ann-preview-body]');
            document.querySelectorAll('[data-ann-preview]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var tpl = document.querySelector('[data-ann-template="' + btn.dataset.annPreview + '"]');
                    previewBody.innerHTML = '';
                    previewBody.appendChild(tpl.content.cloneNode(true));
                    bootstrap.Modal.getOrCreateInstance(previewEl).show();
                });
            });

            // Hapus dengan konfirmasi
            document.querySelectorAll('.js-ann-delete').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (form.dataset.confirmed) return;
                    e.preventDefault();
                    var title = form.dataset.title;
                    var go = function () { form.dataset.confirmed = '1'; form.submit(); };
                    if (!window.Swal) { if (confirm(@json(__('Hapus pengumuman ini?')))) go(); return; }
                    Swal.fire({
                        icon: 'warning',
                        title: @json(__('Hapus pengumuman?')),
                        text: title,
                        showCancelButton: true,
                        confirmButtonText: @json(__('Ya, hapus')),
                        cancelButtonText: @json(__('Batal')),
                        confirmButtonColor: '#d33a3a'
                    }).then(function (r) { if (r.isConfirmed) go(); });
                });
            });
        })();
    </script>
@endpush
