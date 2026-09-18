@php($isTrash = $isTrash ?? false)

<div class="table-responsive">
    <table id="{{ $isTrash ? 'trash-list' : 'admins-list' }}" class="table table-hover table-bordered mb-0">
        <thead class="thead-light">
            <tr>
                <th width="40"><input type="checkbox" data-select-all="#{{ $isTrash ? 'trash-list' : 'admins-list' }}"></th>
                <th>Name</th>
                <th>Email</th>
                <th>Roles</th>
                @if($isTrash)
                    <th>Deleted At</th>
                @else
                    <th>Status</th>
                @endif
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($admins as $admin)
                <tr>
                    <td><input type="checkbox" name="admin_ids[]" value="{{ $admin->id }}" data-row-checkbox></td>
                    <td class="font-weight-bold">
                        {{ $admin->name }}
                        @if(! $isTrash && auth('admin')->id() === $admin->id)
                            <span class="badge badge-danger ml-1">You</span>
                        @endif
                    </td>
                    <td>{{ $admin->email }}</td>
                    <td>
                        @forelse($admin->roles as $role)
                            <span class="badge badge-info mr-1">{{ strtoupper($role->name) }}</span>
                        @empty
                            <span class="text-muted">No role</span>
                        @endforelse
                    </td>
                    @if($isTrash)
                        <td>{{ optional($admin->deleted_at)->format('d M Y, h:i A') }}</td>
                    @else
                        <td><span class="badge badge-{{ $admin->isActive() ? 'success' : 'secondary' }}">{{ ucfirst($admin->status) }}</span></td>
                    @endif
                    <td class="text-right text-nowrap">
                        @if($isTrash)
                            @if(auth('admin')->user()?->can('staff.restore'))
                                <form action="{{ route('admin.admins.restore', $admin->id) }}" method="POST" class="d-inline" data-confirm="Restore this admin?">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success btn-sm" title="Restore"><i class="fas fa-trash-restore"></i></button>
                                </form>
                            @endif
                            @if(auth('admin')->user()?->can('staff.force-delete'))
                                <form action="{{ route('admin.admins.force-delete', $admin->id) }}" method="POST" class="d-inline" data-confirm="Permanently delete this admin? This cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Permanent Delete"><i class="fas fa-times"></i></button>
                                </form>
                            @endif
                        @else
                            @if(auth('admin')->user()?->can('staff.view'))
                                <button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#showAdminModal{{ $admin->id }}" title="View"><i class="fas fa-eye"></i></button>
                            @endif
                            @if(auth('admin')->user()?->can('staff.update'))
                                <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-outline-primary btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(auth('admin')->user()?->can('staff.delete') && auth('admin')->id() !== $admin->id && ! $admin->isSuperAdmin())
                                <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" class="d-inline" data-confirm="Move this admin to trash?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            @endif
                        @endif
                    </td>
                </tr>
                @if(! $isTrash)
                    @include('backoffice.admin.admins.partials.show', ['admin' => $admin])
                @endif
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">{{ $isTrash ? 'Trash is empty.' : 'No admin users found.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
