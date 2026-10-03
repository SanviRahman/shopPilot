@php
    $adminUser = auth('admin')->user() ?? Auth::user();
    $logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout');
    $profile_url = View::getSection('profile_url') ?? config('adminlte.profile_url', 'logout');
    $fallbackAvatar = asset('vendor/adminlte/dist/img/user2-160x160.jpg');
    $avatarUrl = $adminUser && method_exists($adminUser, 'adminlte_image') ? $adminUser->adminlte_image() : $fallbackAvatar;
@endphp

@if(config('adminlte.usermenu_profile_url', false) && $adminUser && method_exists($adminUser, 'adminlte_profile_url'))
    @php
        $profile_url = $adminUser->adminlte_profile_url();
    @endphp
@endif

@if(config('adminlte.use_route_url', false))
    @php
        $profile_url = $profile_url && ! str_starts_with((string) $profile_url, 'http') ? route($profile_url) : $profile_url;
        $logout_url = $logout_url ? route($logout_url) : '';
    @endphp
@else
    @php
        $profile_url = $profile_url ? url($profile_url) : '';
        $logout_url = $logout_url ? url($logout_url) : '';
    @endphp
@endif

<li class="nav-item dropdown user-menu">
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        @if(config('adminlte.usermenu_image'))<img src="{{ $avatarUrl }}" class="user-image img-circle elevation-2" alt="{{ $adminUser?->name ?? 'Admin' }}" onerror="this.onerror=null;this.src='{{ $fallbackAvatar }}';">@endif
        <span @if(config('adminlte.usermenu_image')) class="d-none d-md-inline" @endif>{{ $adminUser?->name ?? 'Admin' }}</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        @if(!View::hasSection('usermenu_header') && config('adminlte.usermenu_header'))
            <li class="user-header {{ config('adminlte.usermenu_header_class', 'bg-primary') }} @if(!config('adminlte.usermenu_image')) h-auto @endif">
                @if(config('adminlte.usermenu_image'))<img src="{{ $avatarUrl }}" class="img-circle elevation-2" alt="{{ $adminUser?->name ?? 'Admin' }}" onerror="this.onerror=null;this.src='{{ $fallbackAvatar }}';">@endif
                <p class="@if(!config('adminlte.usermenu_image')) mt-0 @endif">{{ $adminUser?->name ?? 'Admin' }}@if(config('adminlte.usermenu_desc') && $adminUser && method_exists($adminUser, 'adminlte_desc'))<small>{{ $adminUser->adminlte_desc() }}</small>@endif</p>
            </li>
        @else
            @yield('usermenu_header')
        @endif
        @each('adminlte::partials.navbar.dropdown-item', $adminlte->menu("navbar-user"), 'item')
        @hasSection('usermenu_body')<li class="user-body">@yield('usermenu_body')</li>@endif
        <li class="user-footer">
            @if($profile_url)<a href="{{ $profile_url }}" class="nav-link btn btn-default btn-flat d-inline-block"><i class="fa fa-fw fa-user text-lightblue"></i> {{ __('adminlte::menu.profile') }}</a>@endif
            <a class="btn btn-default btn-flat float-right @if(!$profile_url) btn-block @endif" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-fw fa-power-off text-red"></i> {{ __('adminlte::adminlte.log_out') }}</a>
            <form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">@if(config('adminlte.logout_method')){{ method_field(config('adminlte.logout_method')) }}@endif{{ csrf_field() }}</form>
        </li>
    </ul>
</li>
