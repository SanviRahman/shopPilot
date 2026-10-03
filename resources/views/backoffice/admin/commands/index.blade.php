@extends('layouts.admin')

@section('meta_title', 'System Commands')

@section('page_content')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-terminal mr-1"></i> System Commands</h3>
    </div>

    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-shield-alt mr-1"></i> Commands use <strong>POST requests</strong> with CSRF protection. Database-changing commands are available only in the <code>local</code> environment.
        </div>

        <h5 class="font-weight-bold mb-3"><i class="fas fa-tools mr-1 text-primary"></i> Application Commands</h5>

        @php
            $safeCommands = [
                ['route' => 'admin.command.clear-cache', 'label' => 'Clear Cache', 'icon' => 'fas fa-broom'],
                ['route' => 'admin.command.clear-config', 'label' => 'Clear Config', 'icon' => 'fas fa-cog'],
                ['route' => 'admin.command.clear-route', 'label' => 'Clear Route Cache', 'icon' => 'fas fa-route'],
                ['route' => 'admin.command.clear-view', 'label' => 'Clear View Cache', 'icon' => 'fas fa-eye-slash'],
                ['route' => 'admin.command.optimize-clear', 'label' => 'Optimize Clear', 'icon' => 'fas fa-bolt'],
            ];
        @endphp

        <div class="row">
            @foreach($safeCommands as $command)
                <div class="col-lg-4 col-md-6 mb-3">
                    <form method="POST" action="{{ route($command['route'], ['return_to' => request()->getRequestUri()]) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-block py-3"><i class="{{ $command['icon'] }} mr-1"></i> {{ $command['label'] }}</button>
                    </form>
                </div>
            @endforeach
        </div>

        <hr>

        <h5 class="font-weight-bold mb-3"><i class="fab fa-facebook mr-1 text-primary"></i> Tracking Maintenance</h5>

        <div class="alert alert-light border">
            <i class="fas fa-info-circle text-info mr-1"></i> Clear stored Meta Pixel event logs from the ShopPilot database. This does not delete your Meta Pixel configuration and cannot remove events already sent to Facebook/Meta.
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-6 mb-3">
                <form method="POST" action="{{ route('admin.command.clear-meta-pixel-events', ['return_to' => request()->getRequestUri()]) }}" onsubmit="return confirm('Clear ALL stored Meta Pixel event logs? This action cannot be undone.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-block py-3"><i class="fas fa-chart-line mr-1"></i> Clear Meta Pixel Events</button>
                </form>
                <small class="text-muted d-block mt-2">Deletes stored records from <code>meta_pixel_events</code>. Pixel settings remain unchanged.</small>
            </div>
        </div>

        <hr>

        <h5 class="font-weight-bold mb-3"><i class="fas fa-folder-open mr-1 text-warning"></i> Storage & cPanel Deployment</h5>

        <div class="alert alert-light border">
            <i class="fas fa-info-circle text-info mr-1"></i> Laravel uploads are stored in <code>storage/app/public</code> and normally exposed through <code>public/storage</code>. After cPanel deployment use <strong>Create Storage Link</strong>. If the link is broken, use <strong>Rebuild Storage Link</strong>.
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-6 mb-3">
                <form method="POST" action="{{ route('admin.command.storage-link', ['return_to' => request()->getRequestUri()]) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-success btn-block py-3"><i class="fas fa-link mr-1"></i> Create Storage Link</button>
                </form>
                <small class="text-muted d-block mt-2">Runs <code>php artisan storage:link</code></small>
            </div>

            <div class="col-lg-6 col-md-6 mb-3">
                <form method="POST" action="{{ route('admin.command.storage-link-rebuild', ['return_to' => request()->getRequestUri()]) }}" onsubmit="return confirm('Rebuild the Laravel public storage link?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-block py-3"><i class="fas fa-sync-alt mr-1"></i> Rebuild Storage Link</button>
                </form>
                <small class="text-muted d-block mt-2">Removes the old Laravel storage link and creates it again.</small>
            </div>
        </div>

        <hr>

        <h5 class="font-weight-bold mb-3"><i class="fas fa-cloud-upload-alt mr-1 text-primary"></i> cPanel Production Deployment</h5>

        <div class="alert alert-warning border">
            <i class="fas fa-exclamation-triangle mr-1"></i> Use these buttons only after uploading the new project files and configuring the production <code>.env</code>. These actions never run <code>migrate:fresh</code> and will not intentionally delete existing production data. Because this project uses Closure command routes, <code>route:cache</code> is intentionally not included.
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-6 mb-3">
                <form method="POST" action="{{ route('admin.command.deploy-migrate', ['return_to' => request()->getRequestUri()]) }}" onsubmit="return confirm('Run pending production migrations now?');">@csrf<button type="submit" class="btn btn-outline-success btn-block py-3"><i class="fas fa-database mr-1"></i> Run Production Migrations</button></form>
                <small class="text-muted d-block mt-2">Runs <code>php artisan migrate --force</code>.</small>
            </div>

            <div class="col-lg-6 col-md-6 mb-3">
                <form method="POST" action="{{ route('admin.command.deploy-sync-permissions', ['return_to' => request()->getRequestUri()]) }}" onsubmit="return confirm('Sync ShopPilot roles and permissions on this server?');">@csrf<button type="submit" class="btn btn-outline-info btn-block py-3"><i class="fas fa-user-shield mr-1"></i> Sync Role Permissions</button></form>
                <small class="text-muted d-block mt-2">Runs <code>RolePermissionSeeder</code> and resets the permission cache.</small>
            </div>

            <div class="col-lg-6 col-md-6 mb-3">
                <form method="POST" action="{{ route('admin.command.deploy-config-cache', ['return_to' => request()->getRequestUri()]) }}" onsubmit="return confirm('Build the production configuration cache? Make sure the server .env is correct first.');">@csrf<button type="submit" class="btn btn-outline-primary btn-block py-3"><i class="fas fa-cogs mr-1"></i> Build Config Cache</button></form>
                <small class="text-muted d-block mt-2">Runs <code>php artisan config:cache</code>.</small>
            </div>

            <div class="col-lg-6 col-md-6 mb-3">
                <form method="POST" action="{{ route('admin.command.deploy-view-cache', ['return_to' => request()->getRequestUri()]) }}">@csrf<button type="submit" class="btn btn-outline-primary btn-block py-3"><i class="fas fa-layer-group mr-1"></i> Build View Cache</button></form>
                <small class="text-muted d-block mt-2">Runs <code>php artisan view:cache</code>.</small>
            </div>
        </div>

        <hr>

        <h5 class="font-weight-bold mb-3"><i class="fas fa-database mr-1 text-success"></i> Local Database Commands</h5>

        @if(! $isLocal)
            <div class="alert alert-warning mb-0"><i class="fas fa-lock mr-1"></i> Database migration and seed commands are disabled because <code>APP_ENV</code> is not <code>local</code>.</div>
        @else
            @php
                $dbCommands = [
                    ['route' => 'admin.command.migrate', 'label' => 'Run Migrations', 'class' => 'btn-outline-success', 'icon' => 'fas fa-database', 'warning' => 'Run pending migrations?'],
                    ['route' => 'admin.command.seed', 'label' => 'Seed Database', 'class' => 'btn-outline-info', 'icon' => 'fas fa-seedling', 'warning' => 'Run database seeders?'],
                    ['route' => 'admin.command.migrate-fresh', 'label' => 'Migrate Fresh', 'class' => 'btn-outline-danger', 'icon' => 'fas fa-exclamation-triangle', 'warning' => 'WARNING: This will DELETE all database tables and data. Continue?'],
                    ['route' => 'admin.command.migrate-fresh-seed', 'label' => 'Migrate Fresh & Seed', 'class' => 'btn-danger', 'icon' => 'fas fa-database', 'warning' => 'WARNING: This will DELETE all existing database data, rebuild tables and seed the database. Continue?'],
                ];
            @endphp

            <div class="row">
                @foreach($dbCommands as $command)
                    <div class="col-lg-6 col-md-6 mb-3">
                        <form method="POST" action="{{ route($command['route'], ['return_to' => request()->getRequestUri()]) }}" onsubmit="return confirm(@js($command['warning']));">
                            @csrf
                            <button type="submit" class="btn {{ $command['class'] }} btn-block py-3"><i class="{{ $command['icon'] }} mr-1"></i> {{ $command['label'] }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection