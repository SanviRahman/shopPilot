@php
    $isTrash = $isTrash ?? false;
@endphp
<div class="table-responsive">
    <table class="table table-hover table-striped mb-0">
        <thead><tr><th width="40"><input type="checkbox" id="blogSelectAll"></th><th>Title</th><th>Description</th><th>Author</th><th>Slug</th><th>{{ $isTrash ? 'Deleted At' : 'Created At' }}</th><th width="165" class="text-right">Action</th></tr></thead>
        <tbody>
            @forelse($blogs as $blog)
                <tr>
                    <td><input type="checkbox" value="{{ $blog->id }}" data-blog-checkbox></td>
                    <td><strong>{{ $blog->title }}</strong></td>
                    <td><span title="{{ $blog->description }}">{{ \Illuminate\Support\Str::limit($blog->description ?: 'No description', 70) }}</span></td>
                    <td>@if($blog->author)<span class="badge badge-info">{{ class_basename($blog->author_type) }}: {{ $blog->author->name }}</span>@else<span class="text-muted">System</span>@endif</td>
                    <td><code>{{ $blog->slug }}</code></td>
                    <td>{{ ($isTrash ? $blog->deleted_at : $blog->created_at)?->format('d M Y, h:i A') }}</td>
                    <td class="text-right text-nowrap">
                        @if($isTrash)
                            @can('blogs.restore')<button type="button" class="btn btn-outline-success btn-xs btn-blog-action" data-url="{{ route('admin.blogs.restore', $blog->id) }}" data-method="PATCH" data-confirm-title="Restore Blog?" data-confirm-text="This blog will return to the active list." title="Restore"><i class="fas fa-trash-restore"></i></button>@endcan
                            @can('blogs.force-delete')<button type="button" class="btn btn-outline-danger btn-xs btn-blog-action" data-url="{{ route('admin.blogs.force-delete', $blog->id) }}" data-method="DELETE" data-confirm-title="Permanently Delete?" data-confirm-text="This blog cannot be recovered." title="Permanent Delete"><i class="fas fa-times"></i></button>@endcan
                        @else
                            @can('blogs.view')<button type="button" class="btn btn-outline-info btn-xs btn-show-blog" data-url="{{ route('admin.blogs.show', $blog) }}" title="View"><i class="fas fa-eye"></i></button>@endcan
                            @can('blogs.update')<button type="button" class="btn btn-outline-primary btn-xs btn-edit-blog" data-url="{{ route('admin.blogs.edit', $blog) }}" data-update-url="{{ route('admin.blogs.update', $blog) }}" title="Edit"><i class="fas fa-pen"></i></button>@endcan
                            @can('blogs.delete')<button type="button" class="btn btn-outline-danger btn-xs btn-blog-action" data-url="{{ route('admin.blogs.destroy', $blog) }}" data-method="DELETE" data-confirm-title="Move to Trash?" data-confirm-text="This blog can be restored later." title="Delete"><i class="fas fa-trash"></i></button>@endcan
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">{{ $isTrash ? 'Trash is empty.' : 'No blog posts found.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
