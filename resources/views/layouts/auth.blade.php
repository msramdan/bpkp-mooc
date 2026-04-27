<!DOCTYPE html>
<html lang="id" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light"
    data-header-styles="light" data-menu-styles="light" data-toggled="close">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="{{ config('app.name') }}">
    <meta name="author" content="{{ config('app.brand_display') }}">
    <title>@yield('title', __('Masuk') . ' | ' . config('app.brand_display'))</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link id="style" href="{{ asset('backend') }}/assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('backend') }}/assets/css/styles.css" rel="stylesheet">
    <link href="{{ asset('backend') }}/assets/css/bpkp-theme-overrides.css" rel="stylesheet">
    <link href="{{ asset('backend') }}/assets/icon-fonts/icons.css" rel="stylesheet">
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
    <script src="{{ asset('backend') }}/assets/js/authentication-main.js"></script>
    @stack('css')
</head>

<body class="authentication-background authenticationcover-background position-relative" id="particles-js">
    {{-- Particles di body seperti signin-basic.html. Jangan beri z-index pada .container — hanya blok form/logo (z-3) di atas canvas agar area kosong tetap interaktif. --}}
    @yield('content')

    <script src="{{ asset('backend') }}/assets/libs/%40popperjs/core/umd/popper.min.js"></script>
    <script src="{{ asset('backend') }}/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('backend') }}/assets/libs/particles.js/particles.js"></script>
    <script src="{{ asset('backend') }}/assets/js/basic-password.js"></script>
    <script src="{{ asset('backend') }}/assets/js/show-password.js"></script>
    @stack('scripts')
</body>

</html>
