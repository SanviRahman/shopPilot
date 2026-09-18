@extends('layouts.admin')

@section('meta_title', 'Permissions')

@section('page_content')
    @include('backoffice.admin.permissions.partials.alerts')

    <div class="d-flex flex-wrap align-items-center mb-3">
        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary mr-2 mb-2"><i class="fas fa-plus mr-1"></i>Add New Permission</a>
        <a href="{{ route('admin.permissions.trash') }}" class="btn btn-outline-danger mr-2 mb-2"><i class="fas fa-trash mr-1"></i>Trash Bin</a>
        <form action="{{ route('admin.permissions.bulk-action') }}" method="POST" class="form-inline mb-2" data-confirm-permission-bulk>
            @csrf
            <select name="action" class="form-control mr-2" required>
                <option value="">-- Bulk Actions --</option>
                @if(auth('admin')->user()?->can('permissions.manage'))<option value="delete">Move to Trash</option><option value="restore">Restore</option><option value="force-delete">Permanent Delete</option>@endif
            </select>
            <button type="submit" class="btn btn-secondary">Apply</button>
        </form>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-key mr-1"></i>Permission List</h3></div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.permissions.index') }}" class="row">
                <div class="form-group col-md-3 mb-2"><label for="permission-group">Group Name</label><select name="group_name" id="permission-group" class="form-control"><option value="">All Groups</option>@foreach($groups as $group)<option value="{{ $group }}" @selected(request('group_name') === $group)>{{ $group }}</option>@endforeach</select></div>
                <div class="form-group col-md-6 mb-2"><label for="permission-search">Search Permissions</label><input type="search" name="search" id="permission-search" value="{{ request('search') }}" class="form-control" placeholder="Search permission name..."></div>
                <div class="form-group col-md-3 mb-2 d-flex align-items-end"><button class="btn btn-primary mr-2"><i class="fas fa-search mr-1"></i>Search</button><a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">Reset</a></div>
            </form>
        </div>

        @include('backoffice.admin.permissions.partials.table', ['isTrash' => false])

        @if($permissions->hasPages())<div class="card-footer">{{ $permissions->links() }}</div>@endif
    </div>

    @include('backoffice.admin.permissions.partials.form', ['permission' => $formPermission, 'groups' => $groups, 'mode' => $formMode, 'openForm' => $openForm])
@endsection

@push('js')
    @include('backoffice.admin.permissions.partials.script')
@endpush
