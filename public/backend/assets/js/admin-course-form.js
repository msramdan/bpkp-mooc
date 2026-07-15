(function () {
    'use strict';

    document.addEventListener('change', function (event) {
        var input = event.target;
        if (!input || input.type !== 'file' || !input.hasAttribute('data-thumb-preview')) {
            return;
        }

        var previewId = input.getAttribute('data-thumb-preview');
        var preview = document.getElementById(previewId);
        if (!preview || !input.files || !input.files[0]) {
            return;
        }

        var file = input.files[0];
        if (!file.type.startsWith('image/')) {
            return;
        }

        var url = URL.createObjectURL(file);
        preview.src = url;
        preview.onload = function () {
            URL.revokeObjectURL(url);
        };
    });
})();
