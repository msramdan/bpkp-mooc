{{-- Bootstrap colored toast (Zynix). Global: BpkpBootstrapToast.show(message, variant, title, options). --}}
<script>
    (function(w) {
        'use strict';
        if (w.BpkpBootstrapToast) {
            return;
        }

        var logoUrl = @json(asset('backend').'/assets/images/brand-logos/toggle-dark.png');
        var closeLabel = @json(__('Close'));
        var appName = @json(config('app.name'));
        var allowed = ['primary', 'secondary', 'success', 'danger', 'warning', 'info'];
        var defaultDelay = 5500;

        function esc(s) {
            if (s == null) {
                return '';
            }
            var d = document.createElement('div');
            d.textContent = String(s);
            return d.innerHTML;
        }

        w.BpkpBootstrapToast = {
            show: function(message, variant, title, options) {
                if (typeof bootstrap === 'undefined' || !bootstrap.Toast) {
                    return;
                }
                var stack = document.getElementById('bpkpToastStack');
                if (!stack) {
                    return;
                }
                variant = allowed.indexOf(variant) !== -1 ? variant : 'success';
                title = title != null && title !== '' ? String(title) : appName;
                options = options || {};
                var delay = typeof options.delay === 'number' ? options.delay : defaultDelay;

                var html =
                    '<div class="toast colored-toast bg-' + variant + '-transparent" role="alert" aria-live="polite" aria-atomic="true" data-bs-autohide="true" data-bs-delay="' +
                    delay + '">' +
                    '<div class="toast-header bg-' + variant + ' text-fixed-white">' +
                    '<img class="rounded me-2" src="' + logoUrl + '" width="20" height="20" alt="">' +
                    '<strong class="me-auto">' + esc(title) + '</strong>' +
                    '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="' +
                    esc(closeLabel) + '"></button></div>' +
                    '<div class="toast-body">' + esc(message) + '</div></div>';

                stack.insertAdjacentHTML('beforeend', html);
                var el = stack.lastElementChild;
                if (!el) {
                    return;
                }
                el.addEventListener('hidden.bs.toast', function() {
                    el.remove();
                });
                bootstrap.Toast.getOrCreateInstance(el).show();
            }
        };
    })(window);
</script>
