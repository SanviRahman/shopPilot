@php($isTrash = $isTrash ?? false)
<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-roles-list' : 'roles-list' }}" class="table table-hover table-bordered mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40"><input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-roles-list' : 'roles-list' }}"></th>
                <th>Role Name</th><th>Guard Name</th><th>Permissions Count</th><th>Users Count</th>
                @if($isTrash)<th>Deleted At</th>@endif
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roles as $role)
                <tr>
                    <td><input type="checkbox" name="role_ids[]" value="{{ $role->id }}" data-row-checkbox></td>
                    <td class="font-weight-bold">
                        <i class="fas fa-shield-alt text-primary mr-1"></i>{{ $role->name }}
                        @if($role->isProtected())<span class="badge badge-danger ml-1">Protected</span>@endif
                    </td>
                    <td><span class="badge badge-secondary">{{ strtoupper($role->guard_name) }}</span></td>
                    <td><span class="badge badge-info">{{ $role->permissions_count ?? $role->permissions->count() }}</span></td>
                    <td><span class="badge badge-success">{{ $role->admins_count ?? 0 }}</span></td>
                    @if($isTrash)<td>{{ optional($role->deleted_at)->format('d M Y, h:i A') }}</td>@endif
                    <td class="text-right text-nowrap">
                        @if($isTrash)
                            @if(auth('admin')->user()?->can('roles.manage'))
                                <form action="{{ route('admin.roles.restore', $role->id) }}" method="POST" class="d-inline" data-confirm="Restore this role?">@csrf @method('PATCH')<button class="btn btn-outline-success btn-sm" title="Restore"><i class="fas fa-trash-restore"></i></button></form>
                                @unless($role->isProtected())
                                    <form action="{{ route('admin.roles.force-delete', $role->id) }}" method="POST" class="d-inline" data-confirm="Permanently delete this role?">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" title="Permanent Delete"><i class="fas fa-times"></i></button></form>
                                @endunless
                            @endif
                        @else
                            @if(auth('admin')->user()?->can('roles.view'))<button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#showRoleModal{{ $role->id }}"><i class="fas fa-eye"></i></button>@endif
                            @if(auth('admin')->user()?->can('roles.manage'))<a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i></a>@endif
                            @if(auth('admin')->user()?->can('roles.manage') && ! $role->isProtected())<form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" data-confirm="Move this role to trash?">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button></form>@endif
                        @endif
                    </td>
                </tr>
                @if(! $isTrash) @include('backoffice.admin.roles.partials.show', ['role' => $role]) @endif
            @empty
                <tr><td colspan="{{ $isTrash ? 7 : 6 }}" class="text-center text-muted py-4">{{ $isTrash ? 'Trash is empty.' : 'No roles found.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
