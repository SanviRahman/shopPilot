@php
    $portalVendor = $vendor ?? null;
    $portalStore = $portalVendor?->store;
@endphp

<aside class="vp-sidebar" id="vpSidebar" aria-label="Vendor navigation">
    <a class="vp-brand" href="{{ route('vendor.dashboard') }}">
        <span class="vp-brand-mark"><i class="fas fa-store"></i></span>
        <span class="vp-brand-copy">
            <strong>ShopPilot</strong>
            <small>SELLER WORKSPACE</small>
        </span>
    </a>

    <div class="vp-store-switcher">
        <div class="vp-store-avatar">
            @if ($portalStore?->getFirstMediaUrl('store_logo'))
                <img src="{{ $portalStore->getFirstMediaUrl('store_logo') }}" alt="{{ $portalStore->name }}">
            @else
                <i class="fas fa-store"></i>
            @endif
        </div>
        <div class="vp-store-meta">
            <strong>{{ $portalStore?->name ?? 'Your store' }}</strong>
            <span>
                <i class="vp-status-dot {{ $portalVendor?->status === 'approved' ? 'is-live' : '' }}"></i>
                {{ $portalVendor?->status === 'approved' ? 'Store approved' : 'Seller workspace' }}
            </span>
        </div>
    </div>

    <nav class="vp-nav">
        <span class="vp-nav-label">Workspace</span>
        <a href="{{ route('vendor.dashboard') }}" class="{{ request()->routeIs('vendor.dashboard') ? 'is-active' : '' }}">
            <i class="fas fa-th-large"></i><span>Overview</span>
        </a>

        <span class="vp-nav-label">Catalogue</span>
        <a href="{{ route('vendor.products.index') }}" class="{{ request()->routeIs('vendor.products.*') ? 'is-active' : '' }}">
            <i class="fas fa-box-open"></i><span>Products</span>
        </a>
        <a href="{{ route('vendor.inventory.index') }}" class="{{ request()->routeIs('vendor.inventory.*') ? 'is-active' : '' }}">
            <i class="fas fa-layer-group"></i><span>Inventory</span>
        </a>
        <a href="{{ route('vendor.orders.index') }}" class="{{ request()->routeIs('vendor.orders.*') ? 'is-active' : '' }}">
            <i class="fas fa-shopping-bag"></i><span>Orders</span>
        </a>

        <span class="vp-nav-label">Store</span>
        <a href="{{ route('vendor.store.edit') }}" class="{{ request()->routeIs('vendor.store.*') ? 'is-active' : '' }}">
            <i class="fas fa-store-alt"></i><span>Store profile</span>
        </a>
        <a href="{{ route('vendor.profile.edit') }}" class="{{ request()->routeIs('vendor.profile.*') ? 'is-active' : '' }}">
            <i class="fas fa-user-circle"></i><span>Account settings</span>
        </a>
        <a href="{{ route('vendor.finance.index') }}" class="{{ request()->routeIs('vendor.finance.*') ? 'is-active' : '' }}">
            <i class="fas fa-wallet"></i><span>Finance & payouts</span>
        </a>
    </nav>

    <div class="vp-sidebar-bottom">
        <div class="vp-help-card">
            <span class="vp-help-icon"><i class="far fa-life-ring"></i></span>
            <strong>Need a hand?</strong>
            <p>Your store tools will be right here as your business grows.</p>
        </div>
        <a class="vp-back-link" href="{{ route('website.home') }}">
            <i class="fas fa-arrow-left"></i><span>Back to ShopPilot</span>
        </a>
        <form method="POST" action="{{ route('website.logout') }}">
            @csrf
            <button class="vp-logout" type="submit"><i class="fas fa-sign-out-alt"></i><span>Sign out</span></button>
        </form>
    </div>
</aside>
