(function () {
    'use strict';

    document.addEventListener('input', function (event) {
        const input = event.target;
        if (!input?.dataset?.nativeOptionFilter) {
            return;
        }

        const targetId = input.dataset.nativeOptionFilter;
        const select = targetId ? document.getElementById(targetId) : null;
        if (!select) {
            return;
        }

        const query = (input.value || '').trim().toLowerCase();
        Array.prototype.forEach.call(select.options, function (option) {
            const text = (option.text || '').toLowerCase();
            option.hidden = query !== '' && !text.includes(query);
        });
    });

    document.addEventListener('change', function (event) {
        const input = event.target;
        if (!input?.dataset?.thumbPreview || input.type !== 'file') {
            return;
        }

        const previewId = input.dataset.thumbPreview;
        const preview = document.getElementById(previewId);
        if (!preview || !input.files?.[0]) {
            return;
        }

        const file = input.files[0];
        if (!file.type.startsWith('image/')) {
            return;
        }

        const url = URL.createObjectURL(file);
        preview.src = url;
        preview.onload = function () {
            URL.revokeObjectURL(url);
        };
    });
})();
