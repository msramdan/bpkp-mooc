/*
 * Penyelesaian otomatis materi video & dokumen (MOOC BPKP).
 * - Video: tidak bisa dilompati / dipercepat sebelum selesai; progres dikirim berkala ke server.
 * - PDF : dirender per halaman (pdf.js); halaman yang benar-benar tampil dicatat.
 * Validasi final tetap di server (LessonViewTracker); skrip ini hanya penyampai sinyal.
 */
(function () {
  'use strict';

  var bar = document.getElementById('lessonTrack');
  if (!bar) return;

  var state = {};
  try { state = JSON.parse(bar.dataset.state || '{}'); } catch (e) { state = {}; }

  var url = bar.dataset.url;
  var nextUrl = bar.dataset.nextUrl;
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var completed = !!state.completed;
  var stopped = false;
  var inflight = false;

  var ui = {
    title: bar.querySelector('[data-track-title]'),
    hint: bar.querySelector('[data-track-hint]'),
    meter: bar.querySelector('[data-track-bar]'),
    pct: bar.querySelector('[data-track-pct]'),
    icon: bar.querySelector('[data-track-icon]'),
  };

  function setPercent(p) {
    p = Math.max(0, Math.min(100, Math.floor(p || 0)));
    ui.meter.style.width = p + '%';
    ui.pct.textContent = p + '%';
  }

  function toast(msg) {
    if (window.BpkpBootstrapToast && typeof window.BpkpBootstrapToast.show === 'function') {
      window.BpkpBootstrapToast.show(msg, 'warning');
      return;
    }
    ui.hint.textContent = msg;
  }

  function markDone() {
    if (bar.classList.contains('is-done')) return;
    completed = true;
    bar.classList.add('is-done');
    setPercent(100);
    ui.title.textContent = 'Materi selesai';
    ui.icon.className = 'bi bi-check2-circle';
    ui.hint.innerHTML = '';
    var text = document.createElement('span');
    text.textContent = 'Progres tercatat otomatis. ';
    ui.hint.appendChild(text);
    if (nextUrl) {
      var a = document.createElement('a');
      a.href = nextUrl;
      a.className = 'lesson-track__next';
      a.textContent = 'Lanjut ke materi berikutnya →';
      ui.hint.appendChild(a);
    }
  }

  function send(payload, opts) {
    if (stopped || (completed && !(opts && opts.force))) return Promise.resolve(null);
    if (inflight && !(opts && opts.ended)) return Promise.resolve(null);
    inflight = true;

    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      credentials: 'same-origin',
      keepalive: !!(opts && opts.keepalive),
      body: JSON.stringify(payload),
    }).then(function (r) {
      if (r.status === 419 || r.status === 401) {
        stopped = true;
        ui.hint.textContent = 'Sesi Anda berakhir. Muat ulang halaman agar progres tetap tercatat.';
        return null;
      }
      if (r.status === 403 || r.status === 404) { stopped = true; return null; }
      if (!r.ok) return null; // 429 / 5xx: coba lagi di heartbeat berikutnya
      return r.json();
    }).then(function (d) {
      if (!d) return null;
      state = Object.assign(state, d);
      setPercent(d.percent);
      if (d.completed) markDone();
      return d;
    }).catch(function () { return null; })
      .finally(function () { inflight = false; });
  }

  if (completed) markDone();

  /* ------------------------------------------------------------------ */
  /* Video                                                               */
  /* ------------------------------------------------------------------ */
  var HEARTBEAT_MS = 5000;

  function videoController(getTime, getDuration, seekTo, isPlaying, setRateNormal) {
    var maxWatched = Number(state.max_position) || 0;
    var lastBeat = 0;
    var warnedAt = 0;

    function beat(extra) {
      var d = getDuration();
      var t = getTime();
      if (!isFinite(d) || d <= 0) return;
      lastBeat = Date.now();
      send(Object.assign({ position: Math.round(t * 100) / 100, duration: Math.round(d * 100) / 100 }, extra || {}), extra && extra.ended ? { ended: true } : null)
        .then(function (resp) {
          if (resp && typeof resp.max_position === 'number' && !completed) {
            // Server menolak lompatan: kembali ke posisi terakhir yang diakui.
            if (getTime() > resp.max_position + 8) seekTo(resp.max_position);
            maxWatched = Math.max(Math.min(maxWatched, resp.max_position + 6), resp.max_position);
          }
        });
    }

    return {
      onTick: function () {
        if (completed) return;
        var t = getTime();
        if (t > maxWatched + 2) {
          seekTo(maxWatched);
          if (Date.now() - warnedAt > 4000) {
            warnedAt = Date.now();
            toast('Video belum bisa dilompati. Tonton berurutan sampai selesai.');
          }
          return;
        }
        if (isPlaying()) {
          maxWatched = Math.max(maxWatched, t);
          var d = getDuration();
          if (isFinite(d) && d > 0) setPercent(Math.min(99, (maxWatched / d) * 100));
          if (Date.now() - lastBeat >= HEARTBEAT_MS) beat();
        }
        setRateNormal();
      },
      onPause: function () { if (!completed) beat(); },
      onEnded: function () { if (!completed) beat({ ended: true, position: getDuration() }); },
      initialBeat: function () { if (!completed) beat(); },
      resumeAt: function () { return maxWatched; },
    };
  }

  var player = state.player;

  if (state.kind === 'video' && player === 'html5') {
    var video = document.getElementById('lessonVideo');
    if (video) {
      var ctrl = videoController(
        function () { return video.currentTime || 0; },
        function () { return video.duration; },
        function (t) { video.currentTime = t; },
        function () { return !video.paused && !video.ended; },
        function () { if (video.playbackRate !== 1) video.playbackRate = 1; }
      );
      var onMeta = function () {
        var resume = ctrl.resumeAt();
        if (!completed && resume > 5 && resume < video.duration - 3) video.currentTime = Math.max(0, resume - 2);
        ctrl.initialBeat();
      };
      // Metadata bisa sudah termuat sebelum skrip ini jalan (cache): jalankan langsung.
      if (video.readyState >= 1) onMeta();
      else video.addEventListener('loadedmetadata', onMeta, { once: true });
      video.addEventListener('timeupdate', ctrl.onTick);
      video.addEventListener('seeking', ctrl.onTick);
      video.addEventListener('ratechange', function () { if (!completed && video.playbackRate !== 1) video.playbackRate = 1; });
      video.addEventListener('pause', ctrl.onPause);
      video.addEventListener('ended', ctrl.onEnded);
      document.addEventListener('visibilitychange', function () { if (document.hidden) ctrl.onPause(); });
    }
  }

  if (state.kind === 'video' && player === 'youtube') {
    var iframe = document.getElementById('lessonEmbed');
    var yt = null;
    window.onYouTubeIframeAPIReady = function () {
      yt = new window.YT.Player(iframe, {
        events: {
          onReady: function () {
            var c = videoController(
              function () { return yt.getCurrentTime() || 0; },
              function () { return yt.getDuration(); },
              function (t) { yt.seekTo(t, true); },
              function () { return yt.getPlayerState() === window.YT.PlayerState.PLAYING; },
              function () { if (yt.getPlaybackRate && yt.getPlaybackRate() !== 1) yt.setPlaybackRate(1); }
            );
            c.initialBeat();
            setInterval(c.onTick, 1000);
            yt.addEventListener('onStateChange', function (ev) {
              if (ev.data === window.YT.PlayerState.PAUSED) c.onPause();
              if (ev.data === window.YT.PlayerState.ENDED) c.onEnded();
            });
          },
        },
      });
    };
    var tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(tag);
  }

  if (state.kind === 'video' && player === 'vimeo' && window.Vimeo) {
    var vp = new window.Vimeo.Player(document.getElementById('lessonEmbed'));
    var vt = 0;
    var vd = NaN;
    var playing = false;
    var vc = videoController(
      function () { return vt; },
      function () { return vd; },
      function (t) { vt = t; vp.setCurrentTime(t); },
      function () { return playing; },
      function () {}
    );
    vp.getDuration().then(function (d) { vd = d; vc.initialBeat(); });
    vp.on('timeupdate', function (e) { vt = e.seconds; vd = e.duration; vc.onTick(); });
    vp.on('seeked', function (e) { vt = e.seconds; vc.onTick(); });
    vp.on('play', function () { playing = true; });
    vp.on('pause', function () { playing = false; vc.onPause(); });
    vp.on('ended', function () { playing = false; vc.onEnded(); });
    vp.on('playbackratechange', function (e) { if (!completed && e.playbackRate !== 1) vp.setPlaybackRate(1).catch(function () {}); });
  }

  /* ------------------------------------------------------------------ */
  /* Dokumen                                                             */
  /* ------------------------------------------------------------------ */
  if (state.kind === 'document' && player === 'image') {
    var img = document.getElementById('lessonImage');
    var tries = 0;
    var tick = function () {
      if (completed || tries++ > 6) return;
      send({ total_pages: 1, pages: [1] }).then(function () {
        if (!completed) setTimeout(tick, 2500);
      });
    };
    if (img && img.complete) tick();
    else if (img) img.addEventListener('load', tick);
  }

  if (state.kind === 'document' && player === 'pdf') {
    var box = document.getElementById('lessonPdf');
    var seen = {};
    (state.pages_seen || []).forEach(function (p) { seen[p] = true; });
    var pending = [];
    var total = 0;

    var updateDocUi = function () {
      if (completed || !total) return;
      var count = Object.keys(seen).length;
      ui.title.textContent = 'Halaman dibaca ' + count + ' dari ' + total;
      setPercent(Math.min(99, (count / total) * 100));
    };

    var flush = function () {
      if (!total || completed) return;
      var pages = pending.splice(0, pending.length);
      send({ total_pages: total, pages: pages }).then(function (d) {
        if (!d && pages.length) pending = pages.concat(pending); // gagal: kirim ulang nanti
      });
    };

    if (!window.pdfjsLib) {
      box.innerHTML = '<div class="lesson-pdf__error">Pembaca dokumen gagal dimuat. Periksa koneksi internet lalu muat ulang halaman.</div>';
      return;
    }

    window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    window.pdfjsLib.getDocument({ url: box.dataset.src, withCredentials: true }).promise.then(function (pdf) {
      total = pdf.numPages;
      box.innerHTML = '';
      var frag = document.createDocumentFragment();

      var render = function (wrap) {
        if (wrap.dataset.rendered) return;
        wrap.dataset.rendered = '1';
        pdf.getPage(Number(wrap.dataset.page)).then(function (page) {
          var width = Math.max(280, box.clientWidth - 24);
          var base = page.getViewport({ scale: 1 });
          var scale = width / base.width;
          var vp = page.getViewport({ scale: scale });
          var ratio = window.devicePixelRatio || 1;
          var canvas = document.createElement('canvas');
          canvas.width = Math.floor(vp.width * ratio);
          canvas.height = Math.floor(vp.height * ratio);
          canvas.style.width = vp.width + 'px';
          canvas.style.height = vp.height + 'px';
          wrap.style.minHeight = '';
          wrap.querySelector('.lesson-pdf__ph') && wrap.querySelector('.lesson-pdf__ph').remove();
          wrap.prepend(canvas);
          page.render({ canvasContext: canvas.getContext('2d'), viewport: vp, transform: ratio !== 1 ? [ratio, 0, 0, ratio, 0, 0] : null });
        });
      };

      // Observer 1: render lebih awal (area diperluas 400px) — TIDAK dipakai untuk status "dibaca".
      var renderIo = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) { if (en.isIntersecting) render(en.target); });
      }, { root: box, rootMargin: '400px 0px' });

      // Status "dibaca": tiap 250 ms hitung area halaman yang benar-benar tampil di layar.
      // Halaman dianggap dibaca bila >= 50% halaman (atau >= 60% tinggi layar) tampil total 1 detik.
      var dwell = {};
      var pages = [];
      var POLL = 250;
      setInterval(function () {
        if (completed || document.hidden) return;
        var view = box.getBoundingClientRect();
        pages.forEach(function (wrap) {
          var n = Number(wrap.dataset.page);
          if (seen[n] || !wrap.dataset.rendered) return;
          var r = wrap.getBoundingClientRect();
          var visible = Math.max(0, Math.min(r.bottom, view.bottom) - Math.max(r.top, view.top));
          var enough = visible >= r.height * 0.5 || visible >= view.height * 0.6;
          dwell[n] = enough ? (dwell[n] || 0) + POLL : 0;
          if (dwell[n] >= 1000) {
            seen[n] = true;
            pending.push(n);
            wrap.classList.add('is-seen');
            updateDocUi();
          }
        });
      }, POLL);

      for (var i = 1; i <= total; i++) {
        var wrap = document.createElement('div');
        wrap.className = 'lesson-pdf__page' + (seen[i] ? ' is-seen' : '');
        wrap.dataset.page = i;
        wrap.style.minHeight = '420px';
        wrap.innerHTML = '<div class="lesson-pdf__ph">Halaman ' + i + '</div><span class="lesson-pdf__num">' + i + ' / ' + total + '</span>';
        frag.appendChild(wrap);
        renderIo.observe(wrap);
        pages.push(wrap);
      }
      box.appendChild(frag);
      updateDocUi();

      // Heartbeat pertama (mulai hitung waktu baca) + kirim halaman baru berkala.
      send({ total_pages: total, pages: [] });
      setInterval(function () {
        if (completed) return;
        flush();
      }, 4000);
      window.addEventListener('pagehide', function () {
        if (!completed && pending.length) send({ total_pages: total, pages: pending.splice(0) }, { keepalive: true });
      });
    }).catch(function () {
      box.innerHTML = '<div class="lesson-pdf__error">Dokumen tidak dapat dibuka. Hubungi admin jika masalah berlanjut.</div>';
    });
  }
})();
