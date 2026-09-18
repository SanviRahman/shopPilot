@extends('layouts.admin')

@section('meta_title', 'Admin Users')

@section('page_content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center mb-3">
        <a href="{{ route('admin.admins.create') }}" class="btn btn-primary mr-2 mb-2">
            <i class="fas fa-plus mr-1"></i>Add New Admin
        </a>
        <a href="{{ route('admin.admins.trash') }}" class="btn btn-outline-danger mr-2 mb-2">
            <i class="fas fa-trash mr-1"></i>Trash Bin
        </a>

        <form action="{{ route('admin.admins.bulk-action') }}" method="POST" class="form-inline mb-2" data-confirm-bulk>
            @csrf
            <select name="action" class="form-control mr-2" required>
                <option value="">-- Bulk Actions --</option>
                @if(auth('admin')->user()?->can('staff.delete'))<option value="delete">Move to Trash</option>@endif
                @if(auth('admin')->user()?->can('staff.restore'))<option value="restore">Restore</option>@endif
                @if(auth('admin')->user()?->can('staff.force-delete'))<option value="force-delete">Permanent Delete</option>@endif
            </select>
            <button type="submit" class="btn btn-secondary">Apply</button>
        </form>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users mr-1"></i>Admin Users List</h3>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.admins.index') }}" class="row">
                <div class="form-group col-md-3 mb-2">
                    <label for="role_id">Filter by Role</label>
                    <select name="role_id" id="role_id" class="form-control">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) request('role_id') === (string) $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6 mb-2">
                    <label for="search">Search Admins</label>
                    <input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name or email...">
                </div>
                <div class="form-group col-md-3 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search mr-1"></i>Search</button>
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        @include('backoffice.admin.admins.partials.table', ['isTrash' => false])

        @if($admins->hasPages())
            <div class="card-footer">{{ $admins->links() }}</div>
        @endif
    </div>

    @include('backoffice.admin.admins.partials.form', [
        'admin' => $formAdmin,
        'roles' => $roles,
        'mode' => $formMode,
        'openForm' => $openForm,
    ])
@endsection

@push('js')
    @include('backoffice.admin.admins.partials.script')
@endpush
