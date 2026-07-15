<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-width="fullwidth" data-menu-styles="light" data-toggled="close">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="LMS BPKP untuk kursus Pusdiklatwas.">
    <meta name="author" content="{{ config('app.name') }}">
    <meta name="keywords" content="MOOC, BPKP, LMS, pusdiklatwas, kursus">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" href="https://php.spruko.com/zynix/zynix/assets/images/brand-logos/favicon.ico"
        type="image/x-icon">
    <link id="style" href="{{ asset('backend') }}/assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('backend') }}/assets/css/styles.css" rel="stylesheet">
    <link href="{{ asset('backend') }}/assets/css/bpkp-theme-overrides.css" rel="stylesheet">
    <link href="{{ asset('backend') }}/assets/css/sidebar-app-overrides.css?v={{ @filemtime(public_path('backend/assets/css/sidebar-app-overrides.css')) ?: time() }}" rel="stylesheet">
    <script>
        (function () {
            var bpkpPrimary = '43, 72, 139';
            var zynixDefaultPurple = '115, 93, 255';
            var prevRgb = [
                '26, 70, 154', '26,70,154',
                '26, 68, 150', '26,68,150',
                '43, 71, 106', '43,71,106',
            ];
            var stored = localStorage.getItem('primaryRGB');
            if (stored === zynixDefaultPurple || stored === '115,93,255' ||
                prevRgb.indexOf(stored) !== -1) {
                localStorage.setItem('primaryRGB', bpkpPrimary);
            }
        })();
    </script>
    <link href="{{ asset('backend') }}/assets/icon-fonts/icons.css" rel="stylesheet">
    <link href="{{ asset('backend') }}/assets/libs/node-waves/waves.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('backend') }}/assets/libs/simplebar/simplebar.min.css">
    <link rel="stylesheet" href="{{ asset('backend') }}/assets/libs/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('backend') }}/assets/libs/%40simonwep/pickr/themes/nano.min.css">
    <link rel="stylesheet"
        href="{{ asset('backend') }}/assets/libs/%40tarekraafat/autocomplete.js/css/autoComplete.css">
    <link rel="stylesheet" href="{{ asset('backend') }}/assets/libs/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="{{ asset('backend') }}/assets/libs/sweetalert2/sweetalert2.min.css">
    @stack('css')
    <script src="{{ asset('backend') }}/assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="{{ asset('backend') }}/assets/js/main.js"></script>
</head>
<body>
    <div id="loader">
        <img src="https://php.spruko.com/zynix/zynix/assets/images/media/loader.svg" alt="">
    </div>
    <div class="page">
        @include('layouts.partials.header')
        @include('layouts.partials.sidebar')
        <div class="main-content app-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
        @include('layouts.partials.footer')
    </div>
    <div class="scrollToTop">
        <span class="arrow lh-1"><i class="ti ti-arrow-big-up fs-16"></i></span>
    </div>
    <div id="responsive-overlay"></div>
    <div id="bpkpToastStack" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1100"
        aria-live="polite"></div>
    <script src="{{ asset('backend') }}/assets/libs/%40popperjs/core/umd/popper.min.js"></script>
    <script src="{{ asset('backend') }}/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    @include('partials.js.bpkp-bootstrap-toast-api')
    <script src="{{ asset('backend') }}/assets/libs/node-waves/waves.min.js"></script>
    <script src="{{ asset('backend') }}/assets/libs/simplebar/simplebar.min.js"></script>
    <script src="{{ asset('backend') }}/assets/js/simplebar.js"></script>
    <script src="{{ asset('backend') }}/assets/libs/flatpickr/flatpickr.min.js"></script>
    <script src="{{ asset('backend') }}/assets/libs/%40simonwep/pickr/pickr.es5.min.js"></script>
    <script src="{{ asset('backend') }}/assets/libs/%40tarekraafat/autocomplete.js/autoComplete.min.js"></script>
    <script src="{{ asset('backend') }}/assets/js/sticky.js"></script>
    <script src="{{ asset('backend') }}/assets/js/defaultmenu.js"></script>
    <script>
        window.AppLocale = @json(app()->getLocale());
        window.AppI18n = {
            search: @json(__('Cari...')),
            select: @json(__('Pilih...')),
            noResults: @json(__('Tidak ditemukan')),
            noChoices: @json(__('Tidak ada pilihan')),
        };
    </script>
    <script src="{{ asset('backend') }}/assets/js/custom.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('backend') }}/assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <script>
        // Clear broken/stale service workers (theme/demo leftovers) that spam console on localhost.
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function (regs) {
                regs.forEach(function (reg) { reg.unregister(); });
            }).catch(function () {});
        }
    </script>
    @stack('scripts')
    @include('layouts.partials.flash-toast')
</body>
</html>
