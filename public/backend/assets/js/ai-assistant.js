/* AI Assistant MOOC BPKP — chat client (vanilla JS, tanpa dependensi). */
(function () {
  'use strict';

  var root = document.getElementById('aiChat');
  if (!root) return;

  var els = {
    messages: document.getElementById('aiMessages'),
    welcome: document.getElementById('aiWelcome'),
    form: document.getElementById('aiForm'),
    input: document.getElementById('aiInput'),
    send: document.getElementById('aiSend'),
    notice: document.getElementById('aiNotice'),
    counter: document.getElementById('aiCounter'),
    history: document.getElementById('aiHistory'),
    title: document.getElementById('aiTitle'),
    quota: document.getElementById('aiQuota'),
  };

  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var defaultTitle = els.title.textContent;
  var state = { conversationId: null, busy: false, quota: null, locked: false };

  /* ---------- Util ------------------------------------------------------ */
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  // Markdown ringan yang aman: escape dulu, baru format terbatas. Tidak ada HTML mentah / link.
  function renderMarkdown(text) {
    var lines = escapeHtml(text).split(/\r?\n/);
    var html = [];
    var list = null;

    function inline(s) {
      return s
        .replace(/`([^`]+)`/g, '<code>$1</code>')
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
        .replace(/(^|[^*])\*([^*\n]+)\*/g, '$1<em>$2</em>')
        .replace(/(^|\s)_([^_\n]+)_(?=\s|$|[.,!?])/g, '$1<em>$2</em>');
    }
    function closeList() { if (list) { html.push('</' + list + '>'); list = null; } }

    lines.forEach(function (line) {
      var m;
      if ((m = line.match(/^\s*[-*•]\s+(.*)$/))) {
        if (list !== 'ul') { closeList(); html.push('<ul>'); list = 'ul'; }
        html.push('<li>' + inline(m[1]) + '</li>');
      } else if ((m = line.match(/^\s*\d+[.)]\s+(.*)$/))) {
        if (list !== 'ol') { closeList(); html.push('<ol>'); list = 'ol'; }
        html.push('<li>' + inline(m[1]) + '</li>');
      } else if ((m = line.match(/^#{1,4}\s+(.*)$/))) {
        closeList(); html.push('<h4>' + inline(m[1]) + '</h4>');
      } else if (line.trim() === '') {
        closeList();
      } else {
        closeList(); html.push('<p>' + inline(line) + '</p>');
      }
    });
    closeList();
    return html.join('');
  }

  function api(url, options) {
    options = options || {};
    var headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    if (options.body) {
      headers['Content-Type'] = 'application/json';
      headers['X-CSRF-TOKEN'] = csrf;
    }
    return fetch(url, {
      method: options.method || 'GET',
      headers: headers,
      body: options.body ? JSON.stringify(options.body) : undefined,
      credentials: 'same-origin',
    }).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (data) {
        return { status: res.status, data: data };
      });
    });
  }

  function scrollToBottom() {
    els.messages.scrollTop = els.messages.scrollHeight;
  }

  /* ---------- Quota ---------------------------------------------------- */
  function renderQuota(q) {
    if (!q) return;
    var prev = state.quota;
    state.quota = q;

    var pill = root.querySelector('[data-quota-pill]');
    var remaining = root.querySelector('[data-quota-remaining]');
    var used = root.querySelector('[data-quota-used]');
    var reset = root.querySelector('[data-quota-reset]');
    var fraction = root.querySelector('[data-quota-fraction]');
    var tickets = root.querySelector('[data-quota-tickets]');
    var meter = root.querySelector('[data-quota-meter]');
    var bar = root.querySelector('[data-quota-bar]');

    if (!q.limit_enabled) {
      pill.textContent = 'Tanpa batas harian';
      remaining.textContent = 'Tidak dibatasi';
      used.textContent = q.used + ' pertanyaan';
      reset.textContent = q.reset_label;
      fraction.textContent = '';
      tickets.innerHTML = '';
      tickets.classList.add('d-none');
      meter.classList.add('d-none');
      setLocked(false);
      return;
    }

    pill.textContent = 'Sisa ' + q.remaining + '/' + q.limit;
    pill.classList.toggle('is-empty', q.exhausted);
    remaining.textContent = q.remaining + '/' + q.limit;
    used.textContent = q.used + '/' + q.limit;
    reset.textContent = q.reset_label;
    fraction.textContent = q.remaining + ' tersisa';
    els.quota.classList.toggle('is-empty', q.exhausted);

    if (q.limit <= 12) {
      tickets.classList.remove('d-none');
      meter.classList.add('d-none');
      tickets.style.setProperty('--ai-ticket-count', q.limit);
      if (tickets.children.length !== q.limit) {
        tickets.innerHTML = '';
        for (var i = 0; i < q.limit; i++) {
          var t = document.createElement('span');
          t.className = 'ai-ticket';
          tickets.appendChild(t);
        }
      }
      Array.prototype.forEach.call(tickets.children, function (t, idx) {
        var isUsed = idx >= q.limit - q.used; // tiket disobek dari kanan
        if (isUsed && !t.classList.contains('is-used') && prev) {
          t.classList.add('is-pop');
          setTimeout(function () { t.classList.remove('is-pop'); }, 600);
        }
        t.classList.toggle('is-used', isUsed);
      });
    } else {
      tickets.classList.add('d-none');
      meter.classList.remove('d-none');
      bar.style.width = Math.max(0, Math.min(100, (q.remaining / q.limit) * 100)) + '%';
    }

    if (q.exhausted) {
      setLocked(true);
      showNotice(root.dataset.quotaExceeded, false);
    } else if (state.locked) {
      setLocked(false);
      hideNotice();
    }
  }

  function setLocked(locked) {
    state.locked = locked;
    root.classList.toggle('is-locked', locked);
    els.input.disabled = locked;
    els.send.disabled = locked || state.busy;
    els.input.placeholder = locked ? 'Kuota hari ini sudah habis' : 'Tulis pertanyaan Anda…';
  }

  function showNotice(text, isError) {
    els.notice.textContent = text;
    els.notice.classList.remove('d-none');
    els.notice.classList.toggle('is-error', !!isError);
  }
  function hideNotice() { els.notice.classList.add('d-none'); }

  /* ---------- Messages ------------------------------------------------- */
  function hideWelcome() { if (els.welcome) els.welcome.classList.add('d-none'); }

  function appendMessage(role, content, meta, variant) {
    hideWelcome();
    var wrap = document.createElement('div');
    wrap.className = 'ai-msg ai-msg--' + role + (variant ? ' ai-msg--' + variant : '');

    var body = '<div class="ai-msg__body"><div class="ai-msg__bubble">' +
      (role === 'assistant' ? renderMarkdown(content) : escapeHtml(content)) +
      '</div>' + (meta ? '<div class="ai-msg__meta">' + escapeHtml(meta) + '</div>' : '') + '</div>';

    wrap.innerHTML = role === 'assistant'
      ? '<span class="ai-msg__icon" aria-hidden="true"><i class="ri-sparkling-2-line"></i></span>' + body
      : body;

    els.messages.appendChild(wrap);
    scrollToBottom();
    return wrap;
  }

  function appendTyping() {
    hideWelcome();
    var wrap = document.createElement('div');
    wrap.className = 'ai-msg ai-msg--assistant';
    wrap.innerHTML = '<span class="ai-msg__icon" aria-hidden="true"><i class="ri-sparkling-2-line"></i></span>' +
      '<div class="ai-msg__body"><div class="ai-msg__bubble"><span class="ai-typing">' +
      '<span class="ai-typing__dots"><span></span><span></span><span></span></span>' +
      '<span data-typing-text>Membaca data belajar Anda…</span></span></div></div>';
    els.messages.appendChild(wrap);
    scrollToBottom();

    var steps = ['Membaca data belajar Anda…', 'Menyusun jawaban…', 'Hampir selesai…'];
    var i = 0;
    var timer = setInterval(function () {
      i = Math.min(i + 1, steps.length - 1);
      var t = wrap.querySelector('[data-typing-text]');
      if (t) t.textContent = steps[i];
    }, 2600);
    wrap._stop = function () { clearInterval(timer); wrap.remove(); };
    return wrap;
  }

  function clearMessages() {
    Array.prototype.slice.call(els.messages.querySelectorAll('.ai-msg')).forEach(function (n) { n.remove(); });
    if (els.welcome) els.welcome.classList.remove('d-none');
  }

  /* ---------- History -------------------------------------------------- */
  function markActive() {
    Array.prototype.forEach.call(els.history.querySelectorAll('[data-ai-open]'), function (b) {
      b.classList.toggle('is-active', b.getAttribute('data-ai-open') === state.conversationId);
    });
  }

  function refreshHistory() {
    return api(root.dataset.conversationsUrl).then(function (r) {
      if (!r.data || !r.data.ok) return;
      els.history.innerHTML = '';
      if (!r.data.conversations.length) {
        els.history.innerHTML = '<li class="ai-chat__history-empty">Belum ada percakapan.</li>';
        return;
      }
      r.data.conversations.forEach(function (c) {
        var li = document.createElement('li');
        li.innerHTML = '<button type="button" class="ai-chat__history-item" data-ai-open="' + escapeHtml(c.id) + '" title="' + escapeHtml(c.title) + '">' +
          '<span class="ai-chat__history-text">' + escapeHtml(c.title) + '</span><small>' + escapeHtml(c.last_message_at || '') + '</small></button>';
        els.history.appendChild(li);
      });
      markActive();
    });
  }

  function openConversation(id) {
    if (state.busy) return;
    state.conversationId = id;
    markActive();
    closeSidebar();
    clearMessages();
    hideWelcome();
    var loading = appendTyping();
    api(root.dataset.conversationUrl + '/' + encodeURIComponent(id)).then(function (r) {
      loading._stop();
      if (r.status !== 200 || !r.data.ok) {
        showNotice('Percakapan tidak ditemukan.', true);
        return;
      }
      els.title.textContent = r.data.conversation.title;
      r.data.messages.forEach(function (m) {
        appendMessage(m.role, m.content, m.created_at, m.role === 'assistant' && m.status === 'rejected' ? 'warning' : null);
      });
    });
  }

  function newConversation() {
    if (state.busy) return;
    state.conversationId = null;
    els.title.textContent = defaultTitle;
    clearMessages();
    markActive();
    closeSidebar();
    if (!state.locked) hideNotice();
    els.input.focus();
  }

  /* ---------- Send ----------------------------------------------------- */
  function setBusy(busy) {
    state.busy = busy;
    els.send.disabled = busy || state.locked;
    root.setAttribute('aria-busy', busy ? 'true' : 'false');
  }

  function send(text) {
    text = (text || '').trim();
    if (!text || state.busy || state.locked) return;

    hideNotice();
    var userNode = appendMessage('user', text);
    els.input.value = '';
    autosize();
    setBusy(true);
    var typing = appendTyping();

    api(root.dataset.askUrl, {
      method: 'POST',
      body: { message: text, conversation_id: state.conversationId },
    }).then(function (r) {
      typing._stop();
      var d = r.data || {};
      if (d.quota) renderQuota(d.quota);

      if (r.status === 200 && d.reply) {
        var isNew = !state.conversationId;
        state.conversationId = d.conversation ? d.conversation.id : state.conversationId;
        if (d.conversation) els.title.textContent = d.conversation.title;
        appendMessage('assistant', d.reply.content, d.reply.created_at, d.type === 'rejected' ? 'warning' : null);
        if (isNew) refreshHistory(); else markActive();
        return;
      }

      // Gagal: kembalikan teks ke input agar bisa dikirim ulang.
      userNode.remove();
      if (r.status !== 429 || d.type !== 'quota_exceeded') {
        els.input.value = text;
        autosize();
      }
      if (!els.messages.querySelector('.ai-msg') && els.welcome) els.welcome.classList.remove('d-none');

      if (r.status === 422 && d.errors) {
        showNotice(Object.values(d.errors)[0][0], true);
      } else if (r.status === 419) {
        showNotice('Sesi Anda kedaluwarsa. Muat ulang halaman lalu coba lagi.', true);
      } else {
        showNotice(d.message || 'Terjadi kesalahan. Silakan coba lagi.', r.status !== 429);
      }
      if (r.status === 403) setLocked(true);
    }).catch(function () {
      typing._stop();
      userNode.remove();
      els.input.value = text;
      autosize();
      showNotice('Koneksi terputus. Periksa jaringan Anda lalu coba lagi.', true);
    }).finally(function () {
      setBusy(false);
      if (!state.locked) els.input.focus();
    });
  }

  /* ---------- Composer ------------------------------------------------- */
  var maxLen = parseInt(els.input.getAttribute('maxlength'), 10) || 1000;
  function autosize() {
    els.input.style.height = 'auto';
    els.input.style.height = Math.min(els.input.scrollHeight, 160) + 'px';
    els.counter.textContent = els.input.value.length + '/' + maxLen;
  }

  els.input.addEventListener('input', autosize);
  els.input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
      e.preventDefault();
      send(els.input.value);
    }
  });
  els.form.addEventListener('submit', function (e) { e.preventDefault(); send(els.input.value); });

  root.addEventListener('click', function (e) {
    var suggest = e.target.closest('[data-ai-suggest]');
    if (suggest) { send(suggest.getAttribute('data-ai-suggest')); return; }
    var open = e.target.closest('[data-ai-open]');
    if (open) { openConversation(open.getAttribute('data-ai-open')); return; }
    if (e.target.closest('[data-ai-new]')) { newConversation(); return; }
    if (e.target.closest('[data-ai-toggle-sidebar]')) { root.classList.toggle('is-sidebar-open'); return; }
    if (root.classList.contains('is-sidebar-open') && !e.target.closest('.ai-chat__sidebar')) closeSidebar();
  });

  function closeSidebar() { root.classList.remove('is-sidebar-open'); }

  /* ---------- Init ----------------------------------------------------- */
  try {
    renderQuota(JSON.parse(document.getElementById('aiInitialQuota').textContent));
  } catch (e) {
    if (window.console) console.error('[AI Assistant] gagal memuat kuota awal', e);
    api(root.dataset.quotaUrl).then(function (r) { if (r.data && r.data.quota) renderQuota(r.data.quota); });
  }
  autosize();
  els.input.focus();
})();
