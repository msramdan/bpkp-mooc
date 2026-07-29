(function () {
    'use strict';

    function getCaretCoordinates(textarea, position) {
        const div = document.createElement('div');
        const style = window.getComputedStyle(textarea);
        const properties = [
            'boxSizing', 'width', 'height', 'overflowX', 'overflowY',
            'borderTopWidth', 'borderRightWidth', 'borderBottomWidth', 'borderLeftWidth',
            'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft',
            'fontStyle', 'fontVariant', 'fontWeight', 'fontStretch', 'fontSize',
            'fontSizeAdjust', 'lineHeight', 'fontFamily', 'textAlign', 'textTransform',
            'textIndent', 'textDecoration', 'letterSpacing', 'wordSpacing', 'tabSize', 'whiteSpace'
        ];

        div.style.position = 'absolute';
        div.style.visibility = 'hidden';
        div.style.whiteSpace = 'pre-wrap';
        div.style.wordWrap = 'break-word';
        properties.forEach(function (prop) {
            div.style[prop] = style[prop];
        });

        div.textContent = textarea.value.substring(0, position);
        const span = document.createElement('span');
        span.textContent = textarea.value.substring(position) || '.';
        div.appendChild(span);
        document.body.appendChild(div);

        const coordinates = {
            top: span.offsetTop - textarea.scrollTop,
            left: span.offsetLeft - textarea.scrollLeft
        };
        document.body.removeChild(div);
        return coordinates;
    }

    function parseMentionQuery(value, caret) {
        const before = value.slice(0, caret);
        const match = before.match(/(^|[\s([{])@([^\n@]*)$/);
        if (!match) {
            return null;
        }

        return {
            start: before.length - match[2].length - 1,
            query: match[2].toLowerCase().trimStart()
        };
    }

    function createMenu() {
        const menu = document.createElement('div');
        menu.className = 'pf-mention-menu';
        menu.hidden = true;
        document.body.appendChild(menu);
        return menu;
    }

    function bindMention(textarea) {
        if (!textarea || textarea.dataset.mentionBound === '1') {
            return;
        }
        textarea.dataset.mentionBound = '1';

        let participants = [];
        try {
            participants = JSON.parse(textarea.getAttribute('data-mentionables') || '[]');
        } catch (e) {
            participants = [];
        }

        if (!Array.isArray(participants) || participants.length === 0) {
            return;
        }

        const menu = createMenu();
        let activeIndex = 0;
        let current = null;

        function hideMenu() {
            menu.hidden = true;
            menu.innerHTML = '';
            current = null;
            activeIndex = 0;
        }

        function filtered() {
            if (!current) {
                return [];
            }
            const q = current.query;
            return participants.filter(function (user) {
                return !q || String(user.name || '').toLowerCase().includes(q);
            }).slice(0, 6);
        }

        function renderMenu() {
            const items = filtered();
            if (!current || items.length === 0) {
                hideMenu();
                return;
            }

            menu.innerHTML = items.map(function (user, index) {
                return '<button type="button" class="pf-mention-menu__item' + (index === activeIndex ? ' is-active' : '') + '" data-index="' + index + '" data-name="' + user.name.replace(/"/g, '&quot;') + '">' +
                    '<span class="pf-mention-menu__at">@</span>' +
                    '<span>' + user.name + '</span>' +
                    '</button>';
            }).join('');

            const coords = getCaretCoordinates(textarea, current.start + 1 + current.query.length);
            const rect = textarea.getBoundingClientRect();
            menu.style.left = Math.min(window.scrollX + rect.left + coords.left, window.scrollX + rect.right - 220) + 'px';
            menu.style.top = (window.scrollY + rect.top + coords.top + 28) + 'px';
            menu.hidden = false;
        }

        function insertMention(name) {
            if (!current) {
                return;
            }
            const value = textarea.value;
            const before = value.slice(0, current.start);
            const after = value.slice(textarea.selectionStart);
            const insertion = '@' + name + ' ';
            textarea.value = before + insertion + after;
            const caret = before.length + insertion.length;
            textarea.focus();
            textarea.setSelectionRange(caret, caret);
            hideMenu();
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        }

        textarea.addEventListener('input', function () {
            current = parseMentionQuery(textarea.value, textarea.selectionStart);
            activeIndex = 0;
            renderMenu();
        });

        textarea.addEventListener('keydown', function (event) {
            if (menu.hidden) {
                return;
            }
            const items = filtered();
            if (!items.length) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                renderMenu();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                renderMenu();
            } else if (event.key === 'Enter' || event.key === 'Tab') {
                event.preventDefault();
                insertMention(items[activeIndex].name);
            } else if (event.key === 'Escape') {
                hideMenu();
            }
        });

        menu.addEventListener('mousedown', function (event) {
            const button = event.target.closest('[data-name]');
            if (!button) {
                return;
            }
            event.preventDefault();
            insertMention(button.getAttribute('data-name'));
        });

        document.addEventListener('click', function (event) {
            if (event.target === textarea || menu.contains(event.target)) {
                return;
            }
            hideMenu();
        });
    }

    window.pfBindMention = bindMention;

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('textarea[data-mentionables]').forEach(bindMention);
    });
})();
