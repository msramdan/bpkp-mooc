<script>
    (function(w) {
        'use strict';
        var ns = (w.BpkpDataTableModal = w.BpkpDataTableModal || {});
        if (ns.__detailUtils) {
            return;
        }
        ns.__detailUtils = true;

        ns.escHtml = function(s) {
            if (s == null || s === '') {
                return '';
            }
            var d = document.createElement('div');
            d.textContent = String(s);
            return d.innerHTML;
        };

        ns.escAttr = function(s) {
            if (s == null || s === '') {
                return '';
            }
            return ns.escHtml(s).replace(/"/g, '&quot;');
        };

        ns.jsonDetailFetch = function(url, signal) {
            return fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: signal,
            });
        };
    })(window);
</script>
