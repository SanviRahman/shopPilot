@extends('layouts.admin')

@section('meta_title', 'System Commands')

@section('page_content')
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">System Commands</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-shield-alt mr-1"></i>
                Commands use POST requests with CSRF protection. Database-changing commands are available only in the local environment.
            </div>

            <div class="row">
                @php
                    $safeCommands = [
                        ['route' => 'admin.command.clear-cache', 'label' => 'Clear Cache', 'icon' => 'fas fa-broom'],
                        ['route' => 'admin.command.clear-config', 'label' => 'Clear Config', 'icon' => 'fas fa-cog'],
                        ['route' => 'admin.command.clear-route', 'label' => 'Clear Route Cache', 'icon' => 'fas fa-route'],
                        ['route' => 'admin.command.clear-view', 'label' => 'Clear View Cache', 'icon' => 'fas fa-eye-slash'],
                        ['route' => 'admin.command.optimize-clear', 'label' => 'Optimize Clear', 'icon' => 'fas fa-bolt'],
                    ];
                @endphp

                @foreach($safeCommands as $command)
                    <div class="col-md-4 mb-3">
                        <form method="POST" action="{{ route($command['route'], ['return_to' => request()->getRequestUri()]) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-block py-3">
                                <i class="{{ $command['icon'] }} mr-1"></i> {{ $command['label'] }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <hr>

            <h5 class="font-weight-bold mb-3">Local Database Commands</h5>

            @if(! $isLocal)
                <div class="alert alert-warning mb-0">
                    Database migration/seed commands are disabled because <code>APP_ENV</code> is not <code>local</code>.
                </div>
            @else
                <div class="row">
                    @php
                        $dbCommands = [
                            ['route' => 'admin.command.migrate', 'label' => 'Run Migrations', 'class' => 'btn-outline-success'],
                            ['route' => 'admin.command.seed', 'label' => 'Seed Database', 'class' => 'btn-outline-info'],
                            ['route' => 'admin.command.migrate-fresh', 'label' => 'Migrate Fresh', 'class' => 'btn-outline-danger'],
                            ['route' => 'admin.command.migrate-fresh-seed', 'label' => 'Migrate Fresh & Seed', 'class' => 'btn-danger'],
                        ];
                    @endphp

                    @foreach($dbCommands as $command)
                        <div class="col-md-6 mb-3">
                            <form method="POST" action="{{ route($command['route'], ['return_to' => request()->getRequestUri()]) }}"
                                  onsubmit="return confirm('Run {{ $command['label'] }}? Database data may change.');">
                                @csrf
                                <button type="submit" class="btn {{ $command['class'] }} btn-block py-3">
                                    <i class="fas fa-database mr-1"></i> {{ $command['label'] }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
