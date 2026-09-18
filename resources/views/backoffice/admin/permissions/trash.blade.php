@extends('layouts.admin')

@section('meta_title', 'Permission Trash')

@section('page_content')
    @include('backoffice.admin.permissions.partials.alerts')
    <div class="d-flex flex-wrap align-items-center mb-3">
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-primary mr-2 mb-2"><i class="fas fa-arrow-left mr-1"></i>Back to Permissions</a>
        <form action="{{ route('admin.permissions.bulk-action') }}" method="POST" class="form-inline mb-2" data-confirm-permission-bulk>
            @csrf
            <select name="action" class="form-control mr-2" required><option value="">-- Bulk Actions --</option>@if(auth('admin')->user()?->can('permissions.manage'))<option value="restore">Restore</option><option value="force-delete">Permanent Delete</option>@endif</select>
            <button class="btn btn-secondary">Apply</button>
        </form>
    </div>
    <div class="card card-outline card-danger">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-trash-restore mr-1"></i>Trashed Permissions</h3></div>
        <div class="card-body border-bottom"><form method="GET" action="{{ route('admin.permissions.trash') }}" class="form-inline"><label for="trash-permission-search" class="mr-2">Search</label><input type="search" name="search" id="trash-permission-search" value="{{ request('search') }}" class="form-control mr-2" placeholder="Permission name..."><button class="btn btn-primary mr-2"><i class="fas fa-search"></i></button><a href="{{ route('admin.permissions.trash') }}" class="btn btn-outline-secondary">Reset</a></form></div>
        @include('backoffice.admin.permissions.partials.table', ['isTrash' => true])
        @if($permissions->hasPages())<div class="card-footer">{{ $permissions->links() }}</div>@endif
    </div>
@endsection

@push('js')
    @include('backoffice.admin.permissions.partials.script')
@endpush
