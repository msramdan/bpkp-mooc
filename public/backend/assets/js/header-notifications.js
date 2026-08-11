(function () {
    'use strict';

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content') || '';
        var input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function iconClass(icon) {
        if (icon === 'mention') return 'is-mention ri-at-line';
        if (icon === 'reply') return 'is-reply ri-chat-3-fill';
        if (icon === 'like') return 'is-like ri-thumb-up-fill';
        return 'is-default ri-notification-3-line';
    }

    document.addEventListener('DOMContentLoaded', function () {
        var root = document.getElementById('bpkpNotificationsRoot');
        if (!root) return;

        var pollUrl = root.getAttribute('data-poll-url');
        var readAllUrl = root.getAttribute('data-read-all-url');
        var readTemplate = root.getAttribute('data-read-url-template') || '';
        var listEl = document.getElementById('bpkpNotifList');
        var emptyEl = document.getElementById('bpkpNotifEmpty');
        var badgeEl = document.getElementById('bpkpNotifBadge');
        var pulseEl = document.getElementById('bpkpNotifPulse');
        var chipEl = document.getElementById('bpkpNotifUnreadChip');
        var markAllBtn = document.getElementById('bpkpNotifMarkAll');
        var i18n = window.bpkpNotifI18n || {};

        var state = {
            filter: 'all',
            items: [],
            unread: 0,
            loading: false,
            serverTime: null,
            lastNewIds: {},
        };

        function setUnread(count) {
            state.unread = Math.max(0, parseInt(count, 10) || 0);
            if (badgeEl) {
                badgeEl.textContent = state.unread > 99 ? '99+' : String(state.unread);
                badgeEl.classList.toggle('d-none', state.unread === 0);
            }
            if (pulseEl) {
                pulseEl.classList.toggle('d-none', state.unread === 0);
            }
            if (chipEl) {
                chipEl.textContent = state.unread + ' ' + (i18n.unread || 'belum dibaca');
                chipEl.disabled = state.unread === 0;
            }
        }

        function render() {
            if (!listEl) return;
            var items = state.items;
            if (state.filter === 'unread') {
                items = items.filter(function (n) { return n.is_unread; });
            }

            if (items.length === 0) {
                listEl.innerHTML = '';
                if (emptyEl) emptyEl.classList.remove('d-none');
                return;
            }
            if (emptyEl) emptyEl.classList.add('d-none');

            listEl.innerHTML = items.map(function (n) {
                var avatar = (n.actor && n.actor.avatar) || '';
                var name = (n.actor && n.actor.name) || '';
                return '' +
                    '<li>' +
                    '  <button type="button" class="bpkp-notif-item' + (n.is_unread ? ' is-unread' : '') + '" data-id="' + escapeHtml(n.id) + '" data-url="' + escapeHtml(n.url || '') + '">' +
                    '    <span class="bpkp-notif-avatar-wrap">' +
                    (avatar
                        ? '<img class="bpkp-notif-avatar" src="' + escapeHtml(avatar) + '" alt="' + escapeHtml(name) + '">'
                        : '<span class="bpkp-notif-avatar d-inline-flex align-items-center justify-content-center"><i class="ri-user-3-line"></i></span>') +
                    '      <span class="bpkp-notif-icon ' + iconClass(n.icon) + '" aria-hidden="true"></span>' +
                    '    </span>' +
                    '    <span>' +
                    '      <p class="bpkp-notif-title">' + escapeHtml(n.title || '') + '</p>' +
                    (n.body ? '<p class="bpkp-notif-body">' + escapeHtml(n.body) + '</p>' : '') +
                    (n.meta && n.meta.thread_title ? '<p class="bpkp-notif-body">' + escapeHtml(n.meta.thread_title) + '</p>' : '') +
                    '      <span class="bpkp-notif-time">' + escapeHtml(n.created_human || '') + '</span>' +
                    '    </span>' +
                    '    <span class="bpkp-notif-dot" aria-hidden="true"></span>' +
                    '  </button>' +
                    '</li>';
            }).join('');
        }

        function mergeItems(fresh, prependOnly) {
            if (!Array.isArray(fresh)) return;
            if (!prependOnly || state.items.length === 0) {
                state.items = fresh;
                return;
            }
            var map = {};
            state.items.forEach(function (n) { map[n.id] = true; });
            var added = [];
            fresh.forEach(function (n) {
                if (!map[n.id]) added.push(n);
            });
            if (added.length) {
                state.items = added.concat(state.items).slice(0, 40);
                if (typeof window.showToast === 'function' && document.visibilityState === 'visible') {
                    window.showToast(i18n.newToast || 'Notifikasi baru dari forum', 'info');
                }
            }
        }

        function fetchNotifications(opts) {
            opts = opts || {};
            if (state.loading && !opts.force) return Promise.resolve();
            state.loading = true;

            var url = pollUrl + (pollUrl.indexOf('?') >= 0 ? '&' : '?') +
                'filter=all&limit=20';
            if (opts.since && state.serverTime) {
                url += '&since=' + encodeURIComponent(state.serverTime);
            }

            return fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then(function (res) { return res.ok ? res.json() : Promise.reject(); })
                .then(function (payload) {
                    setUnread(payload.unread_count || 0);
                    state.serverTime = payload.server_time || state.serverTime;
                    mergeItems(payload.items || [], !!opts.since);
                    render();
                })
                .catch(function () { /* silent */ })
                .finally(function () { state.loading = false; });
        }

        function markRead(id) {
            if (!id || !readTemplate) return Promise.resolve();
            var url = readTemplate.replace('__ID__', encodeURIComponent(id));
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
            })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (payload) {
                    if (!payload) return;
                    setUnread(payload.unread_count || 0);
                    state.items = state.items.map(function (n) {
                        if (n.id === id) n.is_unread = false;
                        return n;
                    });
                    render();
                });
        }

        root.querySelectorAll('.bpkp-notif-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                root.querySelectorAll('.bpkp-notif-tab').forEach(function (t) {
                    t.classList.remove('is-active');
                    t.setAttribute('aria-selected', 'false');
                });
                tab.classList.add('is-active');
                tab.setAttribute('aria-selected', 'true');
                state.filter = tab.getAttribute('data-filter') || 'all';
                render();
            });
        });

        if (listEl) {
            listEl.addEventListener('click', function (e) {
                var btn = e.target.closest('.bpkp-notif-item');
                if (!btn) return;
                var id = btn.getAttribute('data-id');
                var url = btn.getAttribute('data-url');
                markRead(id).finally(function () {
                    if (url) window.location.href = url;
                });
            });
        }

        if (markAllBtn) {
            markAllBtn.addEventListener('click', function () {
                fetch(readAllUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    credentials: 'same-origin',
                })
                    .then(function (res) { return res.ok ? res.json() : null; })
                    .then(function (payload) {
                        if (!payload) return;
                        setUnread(0);
                        state.items = state.items.map(function (n) {
                            n.is_unread = false;
                            return n;
                        });
                        render();
                    });
            });
        }

        // Lightweight realtime: poll every 18s when tab visible, slower when hidden.
        var timer = null;
        function schedule() {
            clearTimeout(timer);
            var delay = document.visibilityState === 'visible' ? 18000 : 60000;
            timer = setTimeout(function () {
                fetchNotifications({ since: true }).finally(schedule);
            }, delay);
        }

        fetchNotifications({ force: true }).finally(schedule);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                fetchNotifications({ since: true });
            }
            schedule();
        });

        // Refresh when dropdown opens.
        root.addEventListener('show.bs.dropdown', function () {
            fetchNotifications({ force: true });
        });
    });
})();
