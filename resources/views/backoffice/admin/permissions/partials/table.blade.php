@php($isTrash = $isTrash ?? false)
<div class="table-responsive"><table id="{{ $isTrash ? 'trash-permissions-list' : 'permissions-list' }}" class="table table-hover table-bordered mb-0">
    <thead class="thead-light"><tr><th width="40"><input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-permissions-list' : 'permissions-list' }}"></th><th>ID</th><th>Permission Name</th><th>Guard Name</th><th>Group Name</th><th>{{ $isTrash ? 'Deleted At' : 'Created At' }}</th><th class="text-right">Actions</th></tr></thead>
    <tbody>
    @forelse($permissions as $permission)
        <tr><td><input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" data-row-checkbox></td><td><a href="#" class="font-weight-bold">#{{ $permission->id }}</a></td><td class="font-weight-bold">{{ $permission->name }}</td><td><span class="badge badge-secondary">{{ $permission->guard_name }}</span></td><td><span class="badge badge-light border"><i class="fas fa-layer-group text-primary mr-1"></i>{{ $permission->group_name ?: 'Other' }}</span></td><td>{{ optional($isTrash ? $permission->deleted_at : $permission->created_at)->format('d M Y, h:i A') }}</td><td class="text-right text-nowrap">
            @if($isTrash)
                @if(auth('admin')->user()?->can('permissions.manage'))<form action="{{ route('admin.permissions.restore', $permission->id) }}" method="POST" class="d-inline" data-confirm="Restore this permission?">@csrf @method('PATCH')<button class="btn btn-outline-success btn-sm" title="Restore"><i class="fas fa-trash-restore"></i></button></form><form action="{{ route('admin.permissions.force-delete', $permission->id) }}" method="POST" class="d-inline" data-confirm="Permanently delete this permission?">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" title="Permanent Delete"><i class="fas fa-times"></i></button></form>@endif
            @else
                @if(auth('admin')->user()?->can('permissions.view'))<button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#showPermissionModal{{ $permission->id }}"><i class="fas fa-eye"></i></button>@endif
                @if(auth('admin')->user()?->can('permissions.manage'))<a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i></a><form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="d-inline" data-confirm="Move this permission to trash?">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button></form>@endif
            @endif
        </td></tr>
        @if(! $isTrash) @include('backoffice.admin.permissions.partials.show', ['permission' => $permission]) @endif
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">{{ $isTrash ? 'Trash is empty.' : 'No permissions found.' }}</td></tr>
    @endforelse
    </tbody>
</table></div>
