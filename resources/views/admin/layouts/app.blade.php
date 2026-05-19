<!DOCTYPE html>

<html class="loading @auth {{auth()->user()->theam_mode}} @endauth" lang="en" data-textdirection="ltr">

<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/x-icon" href="{{asset('admin/img/logo/logo.png')}}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Title -->
    <title>{{config('app.name')}} | Admin</title>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-8TC4PLYHMB"></script>

    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'G-8TC4PLYHMB');
    </script>

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-PN8KVRSC');
    </script>
    <!-- End Google Tag Manager -->

    @include('admin.layouts.css')
    @yield('css')
</head>

<body>

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PN8KVRSC"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <!-- Preloader -->
    <div id="preloader">
        <div class="loader">
            <svg width="240" height="240" viewBox="0 0 240 240">
                <circle class="loader-ring loader-ring-a" cx="120" cy="120" r="105" fill="none" stroke="#000" stroke-width="20" stroke-dasharray="0 660" stroke-dashoffset="-330" stroke-linecap="round"></circle>
                <circle class="loader-ring loader-ring-b" cx="120" cy="120" r="35" fill="none" stroke="#000" stroke-width="20" stroke-dasharray="0 220" stroke-dashoffset="-110" stroke-linecap="round"></circle>
                <circle class="loader-ring loader-ring-c" cx="85" cy="120" r="70" fill="none" stroke="#000" stroke-width="20" stroke-dasharray="0 440" stroke-linecap="round"></circle>
                <circle class="loader-ring loader-ring-d" cx="155" cy="120" r="70" fill="none" stroke="#000" stroke-width="20" stroke-dasharray="0 440" stroke-linecap="round"></circle>
            </svg>
        </div>

    </div>
    <!-- /Preloader -->
    <div class="flapt-page-wrapper">
        <!-- BEGIN: Main Menu-->
        @include('admin.layouts.sidebar')
        <!-- END: Main Menu-->
        <div class="flapt-page-content">
            <!-- BEGIN: Header-->
            @include('admin.layouts.header')
            <!-- END: Header-->
            <!-- BEGIN: Content-->
            @yield('content')

            <!-- END: Content-->
        </div>
    </div>

    @include('admin.layouts.footer')

    @include('admin.layouts.js')
    @yield('script')
</body>

</html>