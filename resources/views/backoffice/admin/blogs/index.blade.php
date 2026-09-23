@extends('layouts.admin')

@section('meta_title', 'Blog Management')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            <h4 class="font-weight-bold mb-0">
                <i class="fas fa-newspaper text-primary mr-2"></i> All Blogs
            </h4>
        </div>
        <div class="col-12 col-md-auto">
            <a href="{{ route('admin.blogs.trash') }}" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-trash-alt mr-1"></i> Trash Bin
            </a>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white">
            <form id="blogFilterForm" method="GET" action="{{ route('admin.blogs.index') }}" class="form-row align-items-end">
                <div class="col-12 col-md-8 mb-2 mb-md-0">
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Search blog title or slug...">
                </div>
                <div class="col-12 col-md-4 text-md-right">
                    <button type="button" id="btnResetFilter" class="btn btn-light border btn-sm"><i class="fas fa-redo-alt mr-1"></i> Reset</button>
                </div>
            </form>
        </div>

        <form id="blogBulkForm" method="POST" action="{{ route('admin.blogs.bulk-action') }}">
            @csrf
            <div class="card-body border-bottom py-2">
                <div class="d-flex align-items-center">
                    <select name="action" class="custom-select custom-select-sm mr-2" style="width: 180px;">
                        <option value="">Bulk Actions</option>
                        <option value="delete">Move to Trash</option>
                    </select>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt mr-1"></i> Apply</button>
                </div>
            </div>
        </form>

        <div class="card-body p-0 position-relative">
            <div id="blogTableOverlay" class="overlay d-none">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
            <div id="blogTableContainer">
                @include('backoffice.admin.blogs.partials.table', ['blogs' => $blogs, 'isTrash' => false])
            </div>
        </div>

        <div class="card-footer bg-white border-top py-2" id="paginationContainer">
            @if($blogs->hasPages())
                {{ $blogs->links() }}
            @endif
        </div>
    </div>

    {{-- Show Blog Details Modal --}}
    <div class="modal fade" id="showBlogModal" tabindex="-1" role="dialog" aria-labelledby="showBlogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="showBlogModalLabel">
                        <i class="fas fa-newspaper text-primary mr-2"></i> Blog Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="form-group">
                        <label class="text-muted small text-uppercase font-weight-bold">Title</label>
                        <p id="modal-blog-title" class="font-weight-bold text-dark h5 mb-0"></p>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="text-muted small text-uppercase font-weight-bold">Slug</label>
                            <p class="mb-0"><code id="modal-blog-slug"></code></p>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-muted small text-uppercase font-weight-bold">Author</label>
                            <p class="mb-0"><span id="modal-blog-author" class="badge badge-info"></span></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="text-muted small text-uppercase font-weight-bold">Created At</label>
                            <p id="modal-blog-created" class="text-muted mb-0"></p>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="text-muted small text-uppercase font-weight-bold">Updated At</label>
                            <p id="modal-blog-updated" class="text-muted mb-0"></p>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-muted small text-uppercase font-weight-bold">Content</label>
                        <div id="modal-blog-content" class="p-3 bg-light rounded border text-break" style="white-space: pre-line;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    @include('backoffice.admin.blogs.partials.script', ['fetchUrl' => route('admin.blogs.index')])
@endpush