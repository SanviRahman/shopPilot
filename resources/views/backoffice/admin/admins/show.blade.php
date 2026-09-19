@extends('layouts.admin')

@section('meta_title', 'Admin Details')

@section('page_content')
    <div class="card card-outline card-info">
        <div class="card-header"><h3 class="card-title">Admin Details</h3></div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $admin->name }}</dd>
                <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $admin->email }}</dd>
                <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ ucfirst($admin->status) }}</dd>
                <dt class="col-sm-3">Roles</dt><dd class="col-sm-9">{{ $admin->roles->pluck('name')->join(', ') ?: 'No role' }}</dd>
                <dt class="col-sm-3">Created At</dt><dd class="col-sm-9">{{ optional($admin->created_at)->format('d M Y, h:i A') }}</dd>
            </dl>
        </div>
    </div>
@endsection
