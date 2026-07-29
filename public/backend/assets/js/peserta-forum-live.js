(function () {
    'use strict';

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            return meta.getAttribute('content');
        }
        const input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    function toast(message, type) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type || 'success');
            return;
        }
        // Lightweight fallback
        let box = document.querySelector('.pf-live-toast');
        if (!box) {
            box = document.createElement('div');
            box.className = 'pf-live-toast';
            document.body.appendChild(box);
        }
        box.textContent = message;
        box.classList.add('is-visible', type === 'error' ? 'is-error' : 'is-ok');
        clearTimeout(box._timer);
        box._timer = setTimeout(function () {
            box.classList.remove('is-visible');
        }, 2600);
    }

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function replyHtml(reply) {
        return '' +
            '<div class="pf-reply' + (reply.is_admin ? ' is-admin' : '') + '" data-reply-id="' + escapeHtml(reply.id) + '">' +
            '  <img src="' + escapeHtml(reply.user.avatar) + '" alt="' + escapeHtml(reply.user.name) + '" class="pf-avatar pf-avatar--sm">' +
            '  <div class="pf-reply__bubble">' +
            '    <div class="pf-reply__meta">' +
            '      <strong>' + escapeHtml(reply.user.name) + '</strong>' +
            (reply.is_admin ? '      <span class="pf-badge pf-badge--sm">' + escapeHtml(window.pfForumI18n.admin) + '</span>' : '') +
            '      <time>' + escapeHtml(reply.created_at) + '</time>' +
            (reply.can_edit ? '      <button type="button" class="pf-link-btn js-reply-edit" data-body="' + escapeHtml(reply.body) + '" data-update-url="' + escapeHtml(reply.update_url) + '">' + escapeHtml(window.pfForumI18n.edit) + '</button>' : '') +
            '    </div>' +
            '    <div class="pf-reply__text" data-reply-body>' + (reply.body_html || escapeHtml(reply.body)) + '</div>' +
            '  </div>' +
            '</div>';
    }

    function ensureRepliesBox(post) {
        let box = post.querySelector('.pf-replies');
        if (box) {
            return box;
        }
        box = document.createElement('div');
        box.className = 'pf-replies';
        const replyForm = post.querySelector('.pf-reply-box');
        post.insertBefore(box, replyForm);
        return box;
    }

    function threadHtml(thread, auth) {
        const mentionJson = JSON.stringify(thread.mentionables || []).replace(/'/g, '&#39;');
        return '' +
            '<article class="pf-post' + (thread.is_admin ? ' is-admin' : '') + '" data-thread-id="' + escapeHtml(thread.id) + '">' +
            '  <header class="pf-post__head">' +
            '    <img src="' + escapeHtml(thread.user.avatar) + '" alt="' + escapeHtml(thread.user.name) + '" class="pf-avatar pf-avatar--lg">' +
            '    <div class="pf-post__who">' +
            '      <div class="pf-post__name-row">' +
            '        <strong class="pf-post__name">' + escapeHtml(thread.user.name) + '</strong>' +
            (thread.is_admin ? '        <span class="pf-badge">' + escapeHtml(window.pfForumI18n.admin) + '</span>' : '') +
            '      </div>' +
            '      <time class="pf-post__time">' + escapeHtml(thread.created_at) + '</time>' +
            '    </div>' +
            '    <span class="pf-post__stat"><i class="bi bi-chat-left-text"></i> <span data-reply-count>' + (thread.replies_count || 0) + '</span></span>' +
            '  </header>' +
            '  <div class="pf-post__toolbar">' +
            '    <h3 class="pf-post__title">' + escapeHtml(thread.title) + '</h3>' +
            (thread.can_edit ? '    <button type="button" class="pf-link-btn js-thread-edit" data-title="' + escapeHtml(thread.title) + '" data-body="' + escapeHtml(thread.body) + '" data-update-url="' + escapeHtml(thread.update_url) + '"><i class="bi bi-pencil-square me-1"></i>' + escapeHtml(window.pfForumI18n.edit) + '</button>' : '') +
            '  </div>' +
            '  <p class="pf-post__body" data-thread-body>' + (thread.body_html || escapeHtml(thread.body)) + '</p>' +
            '  <div class="pf-replies" hidden></div>' +
            '  <form method="POST" action="' + escapeHtml(thread.reply_url) + '" class="pf-reply-box js-forum-reply-form">' +
            '    <img src="' + escapeHtml(auth.avatar) + '" alt="' + escapeHtml(auth.name) + '" class="pf-avatar pf-avatar--sm">' +
            '    <div class="pf-reply-box__fields">' +
            '      <textarea name="reply_body" rows="2" class="pf-textarea pf-textarea--compact" data-mentionables=\'' + mentionJson + '\' placeholder="' + escapeHtml(window.pfForumI18n.replyPlaceholder) + '"></textarea>' +
            '      <div class="pf-mention-hint">' + escapeHtml(window.pfForumI18n.mentionHint) + '</div>' +
            '      <button type="submit" class="pf-btn pf-btn--ghost"><i class="bi bi-reply-fill"></i><span>' + escapeHtml(window.pfForumI18n.reply) + '</span></button>' +
            '    </div>' +
            '  </form>' +
            '</article>';
    }

    async function postForm(form) {
        const action = form.getAttribute('action');
        const formData = new FormData(form);
        const response = await fetch(action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: formData,
            credentials: 'same-origin'
        });

        const data = await response.json().catch(function () { return {}; });
        if (!response.ok) {
            const message = data.message
                || (data.errors && Object.values(data.errors)[0] && Object.values(data.errors)[0][0])
                || window.pfForumI18n.error;
            throw new Error(message);
        }
        return data;
    }

    async function putForm(action, payload) {
        const response = await fetch(action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: new URLSearchParams(payload),
            credentials: 'same-origin'
        });

        const data = await response.json().catch(function () { return {}; });
        if (!response.ok) {
            const message = data.message
                || (data.errors && Object.values(data.errors)[0] && Object.values(data.errors)[0][0])
                || window.pfForumI18n.error;
            throw new Error(message);
        }
        return data;
    }

    function bindReplyForms(root) {
        (root || document).querySelectorAll('.js-forum-reply-form').forEach(function (form) {
            if (form.dataset.bound === '1') {
                return;
            }
            form.dataset.bound = '1';
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                const textarea = form.querySelector('textarea[name="reply_body"]');
                if (!textarea || !textarea.value.trim()) {
                    toast(window.pfForumI18n.replyRequired, 'error');
                    return;
                }
                if (button) {
                    button.disabled = true;
                }
                try {
                    const data = await postForm(form);
                    const post = form.closest('.pf-post');
                    const box = ensureRepliesBox(post);
                    box.hidden = false;
                    box.insertAdjacentHTML('beforeend', replyHtml(data.reply));
                    bindEditButtons(box);
                    const countEl = post.querySelector('[data-reply-count]');
                    if (countEl) {
                        countEl.textContent = String(data.replies_count || 0);
                    }
                    if (textarea) {
                        textarea.value = '';
                        if (data.mentionables) {
                            textarea.setAttribute('data-mentionables', JSON.stringify(data.mentionables));
                            if (typeof window.pfBindMention === 'function') {
                                window.pfBindMention(textarea);
                            }
                        }
                    }
                    toast(data.message || window.pfForumI18n.replyOk);
                } catch (error) {
                    toast(error.message || window.pfForumI18n.error, 'error');
                } finally {
                    if (button) {
                        button.disabled = false;
                    }
                }
            });
        });
    }

    function bindCreateForm() {
        const form = document.querySelector('.js-forum-create-form');
        if (!form || form.dataset.bound === '1') {
            return;
        }
        form.dataset.bound = '1';
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
            }
            try {
                const data = await postForm(form);
                const feed = document.querySelector('[data-forum-feed]');
                const empty = document.querySelector('[data-forum-empty]');
                if (empty) {
                    empty.remove();
                }
                if (feed) {
                    feed.insertAdjacentHTML('afterbegin', threadHtml(data.thread, window.pfForumAuth || {}));
                    bindReplyForms(feed);
                    bindEditButtons(feed);
                    const firstTextarea = feed.querySelector('.pf-post textarea[data-mentionables]');
                    if (firstTextarea && typeof window.pfBindMention === 'function') {
                        window.pfBindMention(firstTextarea);
                    }
                }
                const count = document.querySelector('[data-forum-count]');
                if (count) {
                    count.textContent = String((parseInt(count.textContent, 10) || 0) + 1);
                }
                form.reset();
                const modalEl = form.closest('.modal');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
                toast(data.message || window.pfForumI18n.threadOk);
            } catch (error) {
                toast(error.message || window.pfForumI18n.error, 'error');
            } finally {
                if (button) {
                    button.disabled = false;
                }
            }
        });
    }

    function bindEditButtons(root) {
        (root || document).querySelectorAll('.js-thread-edit').forEach(function (button) {
            if (button.dataset.bound === '1') {
                return;
            }
            button.dataset.bound = '1';
            button.addEventListener('click', function () {
                const modalEl = document.querySelector('#forumEditModal, #adminForumEditModal');
                const form = modalEl ? modalEl.querySelector('.js-forum-edit-form') : null;
                if (!modalEl || !form || typeof bootstrap === 'undefined') {
                    return;
                }
                form.dataset.mode = 'thread';
                form.dataset.targetThreadId = button.closest('.pf-post')?.getAttribute('data-thread-id') || '';
                form.setAttribute('action', button.getAttribute('data-update-url'));
                form.querySelector('.js-edit-thread-title')?.classList.remove('d-none');
                form.querySelector('#forumEditTitle, #adminForumEditTitle')?.setAttribute('value', button.getAttribute('data-title') || '');
                const titleInput = form.querySelector('input[name="title"]');
                if (titleInput) titleInput.value = button.getAttribute('data-title') || '';
                const threadBody = form.querySelector('textarea[name="thread_body"]');
                const replyBody = form.querySelector('textarea[name="reply_body"]');
                if (threadBody) {
                    threadBody.classList.remove('d-none');
                    threadBody.value = button.getAttribute('data-body') || '';
                }
                if (replyBody) {
                    replyBody.classList.add('d-none');
                    replyBody.value = '';
                }
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
        });

        (root || document).querySelectorAll('.js-reply-edit').forEach(function (button) {
            if (button.dataset.bound === '1') {
                return;
            }
            button.dataset.bound = '1';
            button.addEventListener('click', function () {
                const modalEl = document.querySelector('#forumEditModal, #adminForumEditModal');
                const form = modalEl ? modalEl.querySelector('.js-forum-edit-form') : null;
                if (!modalEl || !form || typeof bootstrap === 'undefined') {
                    return;
                }
                form.dataset.mode = 'reply';
                form.dataset.targetReplyId = button.closest('[data-reply-id]')?.getAttribute('data-reply-id') || '';
                form.setAttribute('action', button.getAttribute('data-update-url'));
                form.querySelector('.js-edit-thread-title')?.classList.add('d-none');
                const threadBody = form.querySelector('textarea[name="thread_body"]');
                const replyBody = form.querySelector('textarea[name="reply_body"]');
                if (threadBody) {
                    threadBody.classList.add('d-none');
                    threadBody.value = '';
                }
                if (replyBody) {
                    replyBody.classList.remove('d-none');
                    replyBody.value = button.getAttribute('data-body') || '';
                }
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
        });
    }

    function bindEditForm() {
        const form = document.querySelector('.js-forum-edit-form');
        if (!form || form.dataset.bound === '1') {
            return;
        }
        form.dataset.bound = '1';
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            const mode = form.dataset.mode || 'thread';
            const action = form.getAttribute('action');
            const payload = { _method: 'PUT' };
            if (mode === 'thread') {
                payload.title = form.querySelector('input[name="title"]')?.value || '';
                payload.thread_body = form.querySelector('textarea[name="thread_body"]')?.value || '';
            } else {
                payload.reply_body = form.querySelector('textarea[name="reply_body"]')?.value || '';
            }
            if (button) button.disabled = true;
            try {
                const data = await putForm(action, payload);
                if (mode === 'thread' && data.thread) {
                    const post = document.querySelector('.pf-post[data-thread-id="' + form.dataset.targetThreadId + '"]');
                    if (post) {
                        const title = post.querySelector('.pf-post__title');
                        const body = post.querySelector('[data-thread-body]');
                        const edit = post.querySelector('.js-thread-edit');
                        if (title) title.textContent = data.thread.title;
                        if (body) body.innerHTML = data.thread.body_html || escapeHtml(data.thread.body);
                        if (edit) {
                            edit.setAttribute('data-title', data.thread.title);
                            edit.setAttribute('data-body', data.thread.body);
                        }
                    }
                    toast(data.message || window.pfForumI18n.threadUpdated);
                }
                if (mode === 'reply' && data.reply) {
                    const reply = document.querySelector('[data-reply-id="' + form.dataset.targetReplyId + '"]');
                    if (reply) {
                        const body = reply.querySelector('[data-reply-body]');
                        const edit = reply.querySelector('.js-reply-edit');
                        if (body) body.innerHTML = data.reply.body_html || escapeHtml(data.reply.body);
                        if (edit) edit.setAttribute('data-body', data.reply.body);
                    }
                    toast(data.message || window.pfForumI18n.replyUpdated);
                }
                const modalEl = form.closest('.modal');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
            } catch (error) {
                toast(error.message || window.pfForumI18n.error, 'error');
            } finally {
                if (button) button.disabled = false;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindReplyForms(document);
        bindCreateForm();
        bindEditButtons(document);
        bindEditForm();
    });
})();
