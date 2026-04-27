{{-- SweetAlert2 konfirmasi bahaya: .js-delete-confirm di <form> | opsional data-swal-* | API: BpkpSwal.confirmDanger(opts) --}}
<script>
    (function(w) {
        'use strict';
        if (typeof Swal === 'undefined') {
            return;
        }

        var defaults = {
            title: @json(__('Delete this record?')),
            text: @json(__('This action cannot be undone.')),
            confirmButtonText: @json(__('Yes, delete')),
            cancelButtonText: @json(__('Back')),
            icon: 'warning',
            position: 'center',
        };

        w.BpkpSwal = w.BpkpSwal || {};

        function pick(v, fallback) {
            return (v != null && v !== '') ? v : fallback;
        }

        w.BpkpSwal.confirmDanger = function(opts) {
            opts = opts || {};
            return Swal.fire({
                title: pick(opts.title, defaults.title),
                text: pick(opts.text, defaults.text),
                icon: pick(opts.icon, defaults.icon),
                position: pick(opts.position, defaults.position),
                showCancelButton: true,
                focusCancel: true,
                confirmButtonText: pick(opts.confirmButtonText, defaults.confirmButtonText),
                cancelButtonText: pick(opts.cancelButtonText, defaults.cancelButtonText),
                customClass: {
                    popup: 'bpkp-swal-confirm-danger',
                },
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('click', function(e) {
                var btn = e.target.closest('.js-delete-confirm');
                if (!btn) {
                    return;
                }
                e.preventDefault();
                var form = btn.closest('form');
                if (!form) {
                    return;
                }
                var payload = {};
                if (btn.hasAttribute('data-swal-title')) {
                    payload.title = btn.getAttribute('data-swal-title');
                }
                if (btn.hasAttribute('data-swal-text')) {
                    payload.text = btn.getAttribute('data-swal-text');
                }
                if (btn.hasAttribute('data-swal-confirm')) {
                    payload.confirmButtonText = btn.getAttribute('data-swal-confirm');
                }
                if (btn.hasAttribute('data-swal-cancel')) {
                    payload.cancelButtonText = btn.getAttribute('data-swal-cancel');
                }
                w.BpkpSwal.confirmDanger(payload).then(function(result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    })(window);
</script>
