@extends('layouts.admin')

@section('meta_title', 'Admin Trash')

@section('page_content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center mb-3">
        <a href="{{ route('admin.admins.index') }}" class="btn btn-primary mr-2 mb-2">
            <i class="fas fa-arrow-left mr-1"></i>Back to Admins
        </a>
        <form action="{{ route('admin.admins.bulk-action') }}" method="POST" class="form-inline mb-2" data-confirm-bulk>
            @csrf
            <select name="action" class="form-control mr-2" required>
                <option value="">-- Bulk Actions --</option>
                @if(auth('admin')->user()?->can('staff.restore'))<option value="restore">Restore</option>@endif
                @if(auth('admin')->user()?->can('staff.force-delete'))<option value="force-delete">Permanent Delete</option>@endif
            </select>
            <button type="submit" class="btn btn-secondary">Apply</button>
        </form>
    </div>

    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-trash-restore mr-1"></i>Trashed Admins</h3>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.admins.trash') }}" class="form-inline">
                <label for="trash-search" class="mr-2">Search</label>
                <input type="search" name="search" id="trash-search" value="{{ request('search') }}" class="form-control mr-2" placeholder="Name or email...">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i></button>
                <a href="{{ route('admin.admins.trash') }}" class="btn btn-outline-secondary">Reset</a>
            </form>
        </div>

        @include('backoffice.admin.admins.partials.table', ['isTrash' => true])

        @if($admins->hasPages())<div class="card-footer">{{ $admins->links() }}</div>@endif
    </div>
@endsection

@push('js')
    @include('backoffice.admin.admins.partials.script')
@endpush
