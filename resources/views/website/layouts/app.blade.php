<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ShopPilot - Shop Smart, Live Better')</title>
    <meta name="description" content="@yield('meta_description', 'ShopPilot is your trusted destination for quality products, great prices and a smooth shopping experience.')">

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/website/vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/website/css/style.css') }}">
    @stack('styles')
    <script>document.documentElement.classList.add('js');</script>
</head>
<body>
    <div class="site-loader" id="siteLoader" aria-hidden="true">
        <div class="site-loader-inner">
            <div class="loader-brand-mark"><i class="fas fa-shopping-bag"></i></div>
            <div class="loader-brand">Shop<span>Pilot</span></div>
            <div class="loader-line"><span></span></div>
        </div>
    </div>

    <div class="page-shell" id="pageShell">
    @include('website.partials.header')

    <main>
        @yield('content')
    </main>

    @include('website.partials.footer')
    </div>

    <div class="sp-toast" id="spToast" role="status" aria-live="polite"></div>

    <script src="{{ asset('assets/website/js/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
