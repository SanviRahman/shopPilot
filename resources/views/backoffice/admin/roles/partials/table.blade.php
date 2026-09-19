@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-roles-list' : 'roles-list' }}" class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40" class="text-center align-middle">
                    <input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-roles-list' : 'roles-list' }}">
                </th>
                <th>Role Name</th>
                <th>Guard</th>
                <th>Permissions</th>
                <th>Assigned Users</th>
                @if($isTrash)
                    <th>Deleted At</th>
                @endif
                <th width="150" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roles as $role)
                <tr>
                    <td class="text-center align-middle">
                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" data-row-checkbox>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-light border text-primary font-weight-bold rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 38px; height: 38px;">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <span class="font-weight-600 text-dark">{{ $role->name }}</span>
                                @if(method_exists($role, 'isProtected') && $role->isProtected())
                                    <span class="badge badge-danger badge-pill ml-1 font-weight-normal">Protected</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-light border text-uppercase px-2 py-1">{{ $role->guard_name }}</span>
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-info px-2 py-1">
                            <i class="fas fa-key mr-1"></i>{{ $role->permissions_count ?? $role->permissions->count() }} Perms
                        </span>
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-success px-2 py-1">
                            <i class="fas fa-users mr-1"></i>{{ $role->admins_count ?? 0 }} Staff
                        </span>
                    </td>
                    @if($isTrash)
                        <td class="align-middle">
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ optional($role->deleted_at)->format('d M Y, h:i A') }}</small>
                        </td>
                    @endif
                    <td class="text-right align-middle text-nowrap">
                        @if($isTrash)
                            @can('roles.manage')
                                <button type="button" class="btn btn-outline-success btn-sm btn-action"
                                    data-url="{{ route('admin.roles.restore', $role->id) }}"
                                    data-method="PATCH"
                                    data-confirm-title="Restore Role?"
                                    data-confirm-text="Role '{{ $role->name }}' will be restored to active roles list."
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                                @unless(method_exists($role, 'isProtected') && $role->isProtected())
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-action"
                                        data-url="{{ route('admin.roles.force-delete', $role->id) }}"
                                        data-method="DELETE"
                                        data-confirm-title="Permanently Delete Role?"
                                        data-confirm-text="Role '{{ $role->name }}' will be permanently deleted. This cannot be undone."
                                        title="Permanent Delete">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endunless
                            @endcan
                        @else
                            <div class="btn-group btn-group-sm">
                                @can('roles.view')
                                    <button type="button" class="btn btn-default btn-view-role" data-id="{{ $role->id }}" title="View Permissions">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                @endcan
                                @can('roles.manage')
                                    <button type="button" class="btn btn-default btn-edit-role" data-id="{{ $role->id }}" title="Edit Role">
                                        <i class="fas fa-pen text-primary"></i>
                                    </button>
                                @endcan
                                @can('roles.manage')
                                    @if(! (method_exists($role, 'isProtected') && $role->isProtected()))
                                        <button type="button" class="btn btn-default btn-action"
                                            data-url="{{ route('admin.roles.destroy', $role) }}"
                                            data-method="DELETE"
                                            data-confirm-title="Move Role to Trash?"
                                            data-confirm-text="Are you sure you want to move role '{{ $role->name }}' to trash?"
                                            title="Delete">
                                            <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isTrash ? 7 : 6 }}" class="text-center text-muted py-5">
                        <i class="fas fa-shield-alt fa-3x text-light mb-3 d-block"></i>
                        {{ $isTrash ? 'Trash bin is completely empty.' : 'No roles found.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>