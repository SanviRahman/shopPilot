@extends('layouts.admin')

@section('meta_title', 'Blog Management')

@section('page_content')
<div class="row align-items-center justify-content-between mb-3"><div class="col-12 col-md-auto"><h4 class="font-weight-bold mb-0"><i class="fas fa-newspaper text-primary mr-2"></i> All Blogs</h4><small class="text-muted">Create, edit and manage ShopPilot blog content.</small></div><div class="col-12 col-md-auto mt-2 mt-md-0">@can('blogs.create')<button type="button" class="btn btn-primary btn-sm shadow-sm" id="btnCreateBlog"><i class="fas fa-plus-circle mr-1"></i> Add Blog</button>@endcan <a href="{{ route('admin.blogs.trash') }}" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt mr-1"></i> Trash Bin</a></div></div>
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header bg-white"><form id="blogFilterForm" method="GET" action="{{ route('admin.blogs.index') }}" class="form-row align-items-end"><div class="col-12 col-md-9 mb-2 mb-md-0"><input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Search title, slug, description or content..."></div><div class="col-12 col-md-3 text-md-right"><button type="button" id="btnResetFilter" class="btn btn-light border btn-sm"><i class="fas fa-redo-alt mr-1"></i> Reset</button></div></form></div>
    <form id="blogBulkForm" method="POST" action="{{ route('admin.blogs.bulk-action') }}">@csrf<div class="card-body border-bottom py-2"><div class="d-flex align-items-center"><select name="action" class="custom-select custom-select-sm mr-2" style="width: 180px;"><option value="">Bulk Actions</option>@can('blogs.delete')<option value="delete">Move to Trash</option>@endcan</select><button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt mr-1"></i> Apply</button></div></div></form>
    <div class="card-body p-0 position-relative"><div id="blogTableOverlay" class="overlay d-none"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div><div id="blogTableContainer">@include('backoffice.admin.blogs.partials.table', ['blogs' => $blogs, 'isTrash' => false])</div></div>
    <div class="card-footer bg-white border-top py-2" id="paginationContainer">@if($blogs->hasPages()){{ $blogs->links() }}@endif</div>
</div>
@include('backoffice.admin.blogs.partials.form')
@include('backoffice.admin.blogs.partials.show')
@endsection

@push('js')
@include('backoffice.admin.blogs.partials.script', ['fetchUrl' => route('admin.blogs.index'), 'isTrashPage' => false])
@endpush
