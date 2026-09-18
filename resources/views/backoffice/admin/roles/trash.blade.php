@extends('layouts.admin')

@section('meta_title', 'Role Trash')

@section('page_content')
    @include('backoffice.admin.roles.partials.alerts')

    <div class="d-flex flex-wrap align-items-center mb-3">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-primary mr-2 mb-2"><i class="fas fa-arrow-left mr-1"></i>Back to Roles</a>
        <form action="{{ route('admin.roles.bulk-action') }}" method="POST" class="form-inline mb-2" data-confirm-role-bulk>
            @csrf
            <select name="action" class="form-control mr-2" required>
                <option value="">-- Bulk Actions --</option>
                @can('roles.manage')
                    <option value="restore">Restore</option>
                    <option value="force-delete">Permanent Delete</option>
                @endcan
            </select>
            <button type="submit" class="btn btn-secondary">Apply</button>
        </form>
    </div>

    <div class="card card-outline card-danger">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-trash-restore mr-1"></i>Trashed Roles</h3></div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.roles.trash') }}" class="form-inline">
                <label for="trash-role-search" class="mr-2">Search</label>
                <input type="search" name="search" id="trash-role-search" value="{{ request('search') }}" class="form-control mr-2" placeholder="Search role name...">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i></button>
                <a href="{{ route('admin.roles.trash') }}" class="btn btn-outline-secondary">Reset</a>
            </form>
        </div>

        @include('backoffice.admin.roles.partials.table', ['isTrash' => true])

        @if($roles->hasPages())<div class="card-footer">{{ $roles->links() }}</div>@endif
    </div>
@endsection

@push('js')
    @include('backoffice.admin.roles.partials.script')
@endpush
