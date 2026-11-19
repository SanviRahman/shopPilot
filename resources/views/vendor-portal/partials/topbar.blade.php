@php($portalUser = auth('web')->user())
<header class="vp-topbar">
    <div class="vp-topbar-left">
        <button class="vp-icon-button vp-menu-toggle" type="button" data-vp-sidebar-toggle aria-label="Open navigation">
            <i class="fas fa-bars"></i>
        </button>
        <div class="vp-breadcrumb">
            <span>Workspace</span>
            <i class="fas fa-chevron-right"></i>
            <strong>@yield('breadcrumb_label', 'Overview')</strong>
        </div>
    </div>

    <div class="vp-topbar-right">
        <span class="vp-topbar-context"><i class="fas fa-shield-alt"></i> Seller account</span>
        <a class="vp-user-chip" href="{{ route('vendor.profile.edit') }}">
            <span class="vp-user-avatar">{{ strtoupper(substr($portalUser?->name ?? 'S', 0, 1)) }}</span>
            <span class="vp-user-name">{{ $portalUser?->name ?? 'Seller' }}</span>
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</header>
