<aside class="account-sidebar" data-account-reveal="left">
    <div class="account-profile-mini">
        <div class="account-avatar account-avatar-media">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
            @else
                {{ strtoupper(substr($user->name, 0, 1)) }}
            @endif
        </div>
        <strong data-profile-name>{{ $user->name }}</strong>
        <span data-profile-email>{{ $user->email }}</span>
    </div>

    <nav class="account-nav" aria-label="My account navigation">
        <a href="{{ route('website.account.dashboard') }}" class="{{ request()->routeIs('website.account.dashboard') ? 'active' : '' }}"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="{{ route('website.account.orders') }}" class="{{ request()->routeIs('website.account.orders*') ? 'active' : '' }}"><i class="fas fa-box"></i> My Orders</a>
        <a href="{{ route('website.account.payments') }}" class="{{ request()->routeIs('website.account.payments*') ? 'active' : '' }}"><i class="fas fa-credit-card"></i> Payment Submissions</a>
        <a href="{{ route('website.account.wishlist') }}" class="{{ request()->routeIs('website.account.wishlist*') ? 'active' : '' }}"><i class="fas fa-heart"></i> Wishlist</a>
        <a href="{{ route('website.account.profile.edit') }}" class="{{ request()->routeIs('website.account.profile.*') ? 'active' : '' }}"><i class="fas fa-user-cog"></i> Profile Settings</a>
        <a href="{{ route('website.account.password.edit') }}" class="{{ request()->routeIs('website.account.password.*') ? 'active' : '' }}"><i class="fas fa-lock"></i> Change Password</a>
        <a href="{{ route('website.track-order') }}" class="{{ request()->routeIs('website.track-order*') ? 'active' : '' }}"><i class="fas fa-search-location"></i> Track Order</a>
        <a href="{{ route('website.shop') }}"><i class="fas fa-shopping-bag"></i> Continue Shopping</a>
        <form method="POST" action="{{ route('website.logout') }}" data-ajax-logout>@csrf<button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button></form>
    </nav>
</aside>
