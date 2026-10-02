<aside class="account-sidebar" data-account-reveal="left">
    <div class="account-profile-mini">
        <div class="account-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <strong>{{ $user->name }}</strong>
        <span>{{ $user->email }}</span>
    </div>
    <nav class="account-nav">
        <a href="{{ route('website.account.dashboard') }}" class="{{ request()->routeIs('website.account.dashboard') ? 'active' : '' }}"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="{{ route('website.account.orders') }}" class="{{ request()->routeIs('website.account.orders*') ? 'active' : '' }}"><i class="fas fa-box"></i> My Orders</a>
        <a href="{{ route('website.account.payments') }}" class="{{ request()->routeIs('website.account.payments*') ? 'active' : '' }}"><i class="fas fa-credit-card"></i> Payment Submissions</a>
        <a href="{{ route('website.shop') }}"><i class="fas fa-shopping-bag"></i> Continue Shopping</a>
        <form method="POST" action="{{ route('website.logout') }}">@csrf<button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button></form>
    </nav>
</aside>
