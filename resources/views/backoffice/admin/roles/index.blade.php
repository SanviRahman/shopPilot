@extends('layouts.admin')

@section('meta_title', 'Roles')

@section('page_content')
    @include('backoffice.admin.roles.partials.alerts')

    <div class="d-flex flex-wrap align-items-center mb-3">
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary mr-2 mb-2"><i class="fas fa-plus mr-1"></i>Add New Role</a>
        <a href="{{ route('admin.roles.trash') }}" class="btn btn-outline-danger mr-2 mb-2"><i class="fas fa-trash mr-1"></i>Trash Bin</a>
        <form action="{{ route('admin.roles.bulk-action') }}" method="POST" class="form-inline mb-2" data-confirm-role-bulk>
            @csrf
            <select name="action" class="form-control mr-2" required>
                <option value="">-- Bulk Actions --</option>
                @if(auth('admin')->user()?->can('roles.manage'))
                    <option value="delete">Move to Trash</option>
                    <option value="restore">Restore</option>
                    <option value="force-delete">Permanent Delete</option>
                @endif
            </select>
            <button type="submit" class="btn btn-secondary">Apply</button>
        </form>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-user-tag mr-1"></i>Roles List</h3></div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.roles.index') }}" class="form-inline">
                <label for="role-search" class="mr-2">Search Roles</label>
                <input type="search" name="search" id="role-search" value="{{ request('search') }}" class="form-control mr-2" placeholder="Search role name...">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i></button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Reset</a>
            </form>
        </div>

        @include('backoffice.admin.roles.partials.table', ['isTrash' => false])

        @if($roles->hasPages())<div class="card-footer">{{ $roles->links() }}</div>@endif
    </div>

    @include('backoffice.admin.roles.partials.form', [
        'role' => $formRole,
        'permissions' => $permissions,
        'mode' => $formMode,
        'openForm' => $openForm,
    ])
@endsection

@push('js')
    @include('backoffice.admin.roles.partials.script')
@endpush
