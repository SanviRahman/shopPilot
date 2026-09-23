@extends('layouts.admin')

@section('meta_title', 'View Blog Details')

@section('page_content')
    <div class="row align-items-center justify-content-between mb-3">
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            <h4 class="font-weight-bold mb-0">
                <i class="fas fa-newspaper text-primary mr-2"></i> Blog Details
            </h4>
        </div>
        <div class="col-12 col-md-auto">
            <a href="{{ route('admin.blogs.index') }}" class="btn btn-light border btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Blogs
            </a>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title font-weight-bold mb-0">{{ $blog->title }}</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block font-weight-bold text-uppercase">Slug</small>
                    <code>{{ $blog->slug }}</code>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block font-weight-bold text-uppercase">Author</small>
                    @if($blog->author)
                        <span class="badge badge-info">{{ class_basename($blog->author_type) }}: {{ $blog->author->name }}</span>
                    @else
                        <span class="text-muted">System</span>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block font-weight-bold text-uppercase">Created At</small>
                    <span>{{ $blog->created_at?->format('d M Y, h:i A') }}</span>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block font-weight-bold text-uppercase">Updated At</small>
                    <span>{{ $blog->updated_at?->format('d M Y, h:i A') }}</span>
                </div>
            </div>

            <hr>

            <div class="form-group mb-0">
                <label class="font-weight-bold text-muted small text-uppercase">Content</label>
                <div class="p-3 bg-light rounded border">
                    {!! nl2br(e($blog->content)) ?: '<span class="text-muted">No content provided.</span>' !!}
                </div>
            </div>
        </div>
        <div class="card-footer bg-white text-right">
            <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
@endsection