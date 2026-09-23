@php
    $isTrash = $isTrash ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover table-striped mb-0">
        <thead>
            <tr>
                <th width="40">
                    <input type="checkbox" id="blogSelectAll">
                </th>
                <th>Title</th>
                <th>Author</th>
                <th>Slug</th>
                <th>Created At</th>
                <th width="140" class="text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($blogs as $blog)
                <tr>
                    <td>
                        <input type="checkbox" name="blog_ids[]" value="{{ $blog->id }}" class="blog-checkbox" data-blog-checkbox>
                    </td>
                    <td>
                        <strong>{{ $blog->title }}</strong>
                    </td>
                    <td>
                        @if($blog->author)
                            <span class="badge badge-info">{{ class_basename($blog->author_type) }}: {{ $blog->author->name }}</span>
                        @else
                            <span class="text-muted">System</span>
                        @endif
                    </td>
                    <td><code>{{ $blog->slug }}</code></td>
                    <td>{{ $blog->created_at?->format('d M Y, h:i A') }}</td>
                    <td class="text-right">
                        @if($isTrash)
                            @can('blogs.restore')
                                <form method="POST" action="{{ route('admin.blogs.restore', $blog->id) }}" class="d-inline blog-action-form" data-confirm-title="Restore Blog?" data-confirm-text="This blog will be restored.">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success btn-xs" title="Restore">
                                        <i class="fas fa-trash-restore"></i>
                                    </button>
                                </form>
                            @endcan

                            @can('blogs.force-delete')
                                <form method="POST" action="{{ route('admin.blogs.force-delete', $blog->id) }}" class="d-inline blog-action-form" data-confirm-title="Permanently Delete?" data-confirm-text="This item cannot be recovered.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-xs" title="Permanent Delete">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @endcan
                        @else
                            @can('blogs.view')
                                <button type="button" 
                                    class="btn btn-outline-info btn-xs btn-show-blog" 
                                    data-title="{{ $blog->title }}"
                                    data-slug="{{ $blog->slug }}"
                                    data-author="{{ $blog->author ? class_basename($blog->author_type) . ': ' . $blog->author->name : 'System' }}"
                                    data-created="{{ optional($blog->created_at)->format('d M Y, h:i A') }}"
                                    data-updated="{{ optional($blog->updated_at)->format('d M Y, h:i A') }}"
                                    data-content="{{ $blog->content }}"
                                    title="View Blog">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endcan

                            @can('blogs.delete')
                                <form method="POST" action="{{ route('admin.blogs.destroy', $blog->id) }}" class="d-inline blog-action-form" data-confirm-title="Move to Trash?" data-confirm-text="This blog can be restored later.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-xs" title="Delete">
                                         <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                            @endcan
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No blog posts found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>