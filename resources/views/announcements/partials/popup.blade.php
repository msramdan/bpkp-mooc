{{-- Pop up pengumuman (muncul setelah login). $announcementPopup: Collection<Announcement> --}}
@php $tz = \App\Http\Requests\AnnouncementRequest::TZ; $total = $announcementPopup->count(); @endphp
<div class="modal fade ann-modal" id="annPopup" tabindex="-1" aria-labelledby="annPopupTitle" aria-modal="true" role="dialog"
     data-bs-backdrop="static" data-dismiss-url="{{ route('announcements.dismiss') }}"
     data-ids='@json($announcementPopup->pluck('id')->values())'>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="ann-modal__head">
                <div class="ann-modal__eyebrow">
                    <span class="ann-modal__horn"><i class="ri-megaphone-fill" aria-hidden="true"></i></span>
                    <span id="annPopupTitle">{{ __('Pengumuman') }}</span>
                    @if ($total > 1)
                        <span class="ann-modal__counter"><span data-ann-current>1</span> / {{ $total }}</span>
                    @endif
                </div>
                <button type="button" class="ann-modal__close" data-ann-close aria-label="{{ __('Tutup pengumuman') }}"><i class="ri-close-line"></i></button>
            </div>

            <div class="ann-modal__body">
                @foreach ($announcementPopup as $i => $a)
                    <article class="ann-slide ann-cat--{{ $a->category }} {{ $i === 0 ? 'is-active' : '' }}" data-ann-slide="{{ $i }}" aria-hidden="{{ $i === 0 ? 'false' : 'true' }}">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="ann-chip"><span class="ann-dot"></span>{{ \App\Models\Announcement::CATEGORIES[$a->category] ?? $a->category }}</span>
                            <span class="ann-meta">
                                <span><i class="ri-calendar-event-line"></i>{{ ($a->starts_at ?? $a->created_at)->timezone($tz)->translatedFormat('d M Y') }}</span>
                                @if ($a->ends_at)
                                    <span><i class="ri-timer-line"></i>{{ __('Berlaku s.d. :d', ['d' => $a->ends_at->timezone($tz)->translatedFormat('d M Y, H:i')]) }} WIB</span>
                                @endif
                            </span>
                        </div>
                        <h2 class="ann-slide__title">{{ $a->title }}</h2>
                        @include('announcements.partials.content', ['a' => $a])
                        <div class="pb-3"></div>
                    </article>
                @endforeach
            </div>

            <div class="ann-modal__foot">
                @if ($total > 1)
                    <div class="ann-dots" role="tablist" aria-label="{{ __('Pilih pengumuman') }}">
                        @foreach ($announcementPopup as $i => $a)
                            <button type="button" class="{{ $i === 0 ? 'is-active' : '' }}" data-ann-go="{{ $i }}" aria-label="{{ __('Pengumuman :n', ['n' => $i + 1]) }}"></button>
                        @endforeach
                    </div>
                @endif
                <div class="ann-modal__nav">
                    <a href="{{ route('announcements.feed') }}" class="ann-btn-ghost text-decoration-none text-center d-none d-sm-inline-block">{{ __('Lihat semua') }}</a>
                    @if ($total > 1)
                        <button type="button" class="ann-btn-ghost" data-ann-prev disabled><i class="ri-arrow-left-s-line"></i> {{ __('Sebelumnya') }}</button>
                        <button type="button" class="ann-btn-primary" data-ann-next>{{ __('Berikutnya') }} <i class="ri-arrow-right-s-line"></i></button>
                    @else
                        <button type="button" class="ann-btn-primary" data-ann-close>{{ __('Saya mengerti') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        var el = document.getElementById('annPopup');
        if (!el || !window.bootstrap) return;
        var slides = el.querySelectorAll('[data-ann-slide]');
        var dots = el.querySelectorAll('[data-ann-go]');
        var prev = el.querySelector('[data-ann-prev]');
        var next = el.querySelector('[data-ann-next]');
        var cur = el.querySelector('[data-ann-current]');
        var body = el.querySelector('.ann-modal__body');
        var i = 0, n = slides.length, sent = false;
        var labels = { next: @json(__('Berikutnya')), done: @json(__('Saya mengerti')) };

        function show(k) {
            i = Math.max(0, Math.min(n - 1, k));
            slides.forEach(function (s, x) { s.classList.toggle('is-active', x === i); s.setAttribute('aria-hidden', x === i ? 'false' : 'true'); });
            dots.forEach(function (d, x) { d.classList.toggle('is-active', x === i); });
            if (cur) cur.textContent = i + 1;
            if (prev) prev.disabled = i === 0;
            if (next) next.innerHTML = i === n - 1 ? labels.done : labels.next + ' <i class="ri-arrow-right-s-line"></i>';
            if (body) body.scrollTop = 0;
        }

        function dismiss() {
            if (sent) return; sent = true;
            var token = document.querySelector('meta[name=csrf-token]');
            try {
                fetch(el.dataset.dismissUrl, {
                    method: 'POST', credentials: 'same-origin', keepalive: true,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token ? token.content : '' },
                    body: JSON.stringify({ ids: JSON.parse(el.dataset.ids || '[]') })
                }).catch(function () {});
            } catch (e) {}
        }

        var modal = bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static', keyboard: true });
        el.addEventListener('hidden.bs.modal', dismiss);
        el.querySelectorAll('[data-ann-close]').forEach(function (b) { b.addEventListener('click', function () { modal.hide(); }); });
        if (prev) prev.addEventListener('click', function () { show(i - 1); });
        if (next) next.addEventListener('click', function () { if (i === n - 1) { modal.hide(); } else { show(i + 1); } });
        dots.forEach(function (d) { d.addEventListener('click', function () { show(parseInt(d.dataset.annGo, 10)); }); });
        el.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight') show(i + 1);
            if (e.key === 'ArrowLeft') show(i - 1);
        });

        function open() { setTimeout(function () { modal.show(); }, 350); }
        if (document.readyState === 'complete') open(); else window.addEventListener('load', open);
    })();
</script>
