@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-permissions-list' : 'permissions-list' }}" class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40" class="text-center align-middle">
                    <input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-permissions-list' : 'permissions-list' }}">
                </th>
                <th>Permission Name</th>
                <th>Group Name</th>
                <th>Guard</th>
                <th>{{ $isTrash ? 'Deleted At' : 'Created At' }}</th>
                <th width="150" class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($permissions as $permission)
                <tr>
                    <td class="text-center align-middle">
                        <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" data-row-checkbox>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-light border text-info font-weight-bold rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 38px; height: 38px;">
                                <i class="fas fa-key"></i>
                            </div>
                            <div>
                                <span class="font-weight-600 text-dark">{{ $permission->name }}</span>
                                <small class="text-muted d-block">ID: #{{ $permission->id }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-light border text-dark px-2 py-1">
                            <i class="fas fa-layer-group text-primary mr-1"></i>{{ $permission->group_name ?: 'General' }}
                        </span>
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-secondary text-uppercase px-2 py-1">{{ $permission->guard_name }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ optional($isTrash ? $permission->deleted_at : $permission->created_at)->format('d M Y, h:i A') }}</small>
                    </td>
                    <td class="text-right align-middle text-nowrap">
                        @if($isTrash)
                            @can('permissions.manage')
                                <button type="button" class="btn btn-outline-success btn-sm btn-action"
                                    data-url="{{ route('admin.permissions.restore', $permission->id) }}"
                                    data-method="PATCH"
                                    data-confirm-title="Restore Permission?"
                                    data-confirm-text="Permission '{{ $permission->name }}' will be restored to active list."
                                    title="Restore">
                                    <i class="fas fa-trash-restore"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-action"
                                    data-url="{{ route('admin.permissions.force-delete', $permission->id) }}"
                                    data-method="DELETE"
                                    data-confirm-title="Permanently Delete?"
                                    data-confirm-text="Permission '{{ $permission->name }}' will be deleted forever."
                                    title="Permanent Delete">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endcan
                        @else
                            <div class="btn-group btn-group-sm">
                                @can('permissions.view')
                                    <button type="button" class="btn btn-default btn-view-permission" data-id="{{ $permission->id }}" title="View Details">
                                        <i class="fas fa-eye text-info"></i>
                                    </button>
                                @endcan
                                @can('permissions.manage')
                                    <button type="button" class="btn btn-default btn-edit-permission" data-id="{{ $permission->id }}" title="Edit Permission">
                                        <i class="fas fa-pen text-primary"></i>
                                    </button>
                                    <button type="button" class="btn btn-default btn-action"
                                        data-url="{{ route('admin.permissions.destroy', $permission) }}"
                                        data-method="DELETE"
                                        data-confirm-title="Move to Trash?"
                                        data-confirm-text="Are you sure you want to move permission '{{ $permission->name }}' to trash?"
                                        title="Delete">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                @endcan
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fas fa-key fa-3x text-light mb-3 d-block"></i>
                        {{ $isTrash ? 'Trash bin is completely empty.' : 'No permissions found.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>