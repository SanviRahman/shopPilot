@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-list' : 'admins-list' }}" class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40" class="text-center align-middle">
                    <input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-list' : 'admins-list' }}">
                </th>
                <th>Admin</th>
                <th>Roles</th>
                @if($isTrash)
                    <th>Deleted At</th>
                @else
                    <th>Status</th>
                @endif
                <th width="150" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($admins as $admin)
                <tr>
                    <td class="text-center align-middle">
                        <input type="checkbox" name="admin_ids[]" value="{{ $admin->id }}" data-row-checkbox>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white font-weight-bold rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 38px; height: 38px; font-size: 14px;">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-weight-600 text-dark">
                                    {{ $admin->name }}
                                    @if(! $isTrash && auth('admin')->id() === $admin->id)
                                        <span class="badge badge-primary badge-pill ml-1 font-weight-normal">You</span>
                                    @endif
                                </div>
                                <small class="text-muted"><i class="far fa-envelope mr-1"></i>{{ $admin->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle">
                        @forelse($admin->roles as $role)
                            <span class="badge badge-light border text-uppercase px-2 py-1 mr-1">{{ $role->name }}</span>
                        @empty
                            <span class="text-muted small">No role</span>
                        @endforelse
                    </td>
                    <td class="align-middle">
                        @if($isTrash)
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ optional($admin->deleted_at)->format('d M Y, h:i A') }}</small>
                        @else
                            @if($admin->isActive())
                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Active</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1"><i class="fas fa-ban mr-1"></i>Inactive</span>
                            @endif
                        @endif
                    </td>
                    <td class="text-right align-middle text-nowrap">
                        @if($isTrash)
                            @can('staff.restore')
                                <button type="button" class="btn btn-outline-success btn-sm btn-action" 
                                    data-url="{{ route('admin.admins.restore', $admin->id) }}"
                                    data-method="PATCH"
                                    data-confirm-title="Restore Admin?"
                                    data-confirm-text="Admin '{{ $admin->name }}' will be restored to active list."
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                            @endcan
                            @can('staff.force-delete')
                                <button type="button" class="btn btn-outline-danger btn-sm btn-action"
                                    data-url="{{ route('admin.admins.force-delete', $admin->id) }}"
                                    data-method="DELETE"
                                    data-confirm-title="Permanently Delete?"
                                    data-confirm-text="Admin '{{ $admin->name }}' will be deleted forever."
                                    title="Permanent Delete">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endcan
                        @else
                            <div class="btn-group btn-group-sm">
                                @can('staff.view')
                                    <button type="button" class="btn btn-default btn-view-admin" data-id="{{ $admin->id }}" title="View Profile">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                @endcan
                                @can('staff.update')
                                    <button type="button" class="btn btn-default btn-edit-admin" data-id="{{ $admin->id }}" title="Edit Admin">
                                        <i class="fas fa-pen text-primary"></i>
                                    </button>
                                @endcan
                                @can('staff.delete')
                                    @if(auth('admin')->id() !== $admin->id && ! $admin->isSuperAdmin())
                                        <button type="button" class="btn btn-default btn-action"
                                            data-url="{{ route('admin.admins.destroy', $admin) }}"
                                            data-method="DELETE"
                                            data-confirm-title="Move to Trash?"
                                            data-confirm-text="Admin '{{ $admin->name }}' will be moved to trash bin."
                                            title="Move to Trash">
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
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x text-light mb-3 d-block"></i>
                        {{ $isTrash ? 'Trash bin is completely empty.' : 'No admin users found.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>