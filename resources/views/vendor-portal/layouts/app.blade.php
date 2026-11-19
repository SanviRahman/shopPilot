<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vendor Workspace') | {{ config('app.name', 'ShopPilot') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/website/vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor-portal/css/vendor-portal.css') }}">
    @stack('styles')
</head>
<body class="vp-body">
    @php
        $portalVendor = $vendor ?? null;
        $portalStore = $portalVendor?->store;
        $portalUser = auth('web')->user();
    @endphp

    <div class="vp-shell">
        @include('vendor-portal.partials.sidebar')

        <div class="vp-main">
            @include('vendor-portal.partials.topbar')

            <main class="vp-content">
                <header class="vp-page-heading">
                    <div>
                        <div class="vp-eyebrow">@yield('eyebrow', 'SELLER WORKSPACE')</div>
                        <h1>@yield('page_heading', 'Welcome to your store')</h1>
                        <p>@yield('page_description', 'Everything you need to keep your store moving.') </p>
                    </div>
                    <div class="vp-page-actions">
                        @yield('page_actions')
                    </div>
                </header>

                @if (session('success'))
                    <div class="vp-alert vp-alert-success" role="status">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="vp-alert vp-alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="vp-alert vp-alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Please review the highlighted information.</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="vp-footer">
                <span>© {{ now()->year }} {{ config('app.name', 'ShopPilot') }}</span>
                <span>Built to help your business grow.</span>
            </footer>
        </div>

        <button class="vp-sidebar-overlay" type="button" data-vp-sidebar-close aria-label="Close navigation"></button>
    </div>

    <script src="{{ asset('assets/vendor-portal/js/vendor-portal.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
