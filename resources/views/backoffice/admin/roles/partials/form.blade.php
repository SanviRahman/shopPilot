@php
    $isEdit = $mode === 'edit' && $role;
    $action = $isEdit ? route('admin.roles.update', $role) : route('admin.roles.store');
    $selectedPermissions = old('permissions', $role?->permissions?->modelKeys() ?? []);
    $permissionGroups = $permissions->groupBy(fn ($permission) => $permission->group_name ?: 'Other');
@endphp
<div class="modal fade" id="roleFormModal" tabindex="-1" role="dialog" aria-hidden="true" data-open-form="{{ $openForm ? 'true' : 'false' }}">
    <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
        <form action="{{ $action }}" method="POST" autocomplete="off">
            @csrf @if($isEdit) @method('PUT') @endif
            <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-user-tag mr-1"></i>{{ $isEdit ? 'Update Role' : 'Create Role' }}</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <div class="form-group"><label for="role-name">Role Name <span class="text-danger">*</span></label><input id="role-name" name="name" value="{{ old('name', $role?->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. manager" required>@error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                <div class="form-group"><label>Permissions</label><div class="border rounded p-3" style="max-height:360px;overflow:auto;">
                    @foreach($permissionGroups as $group => $groupPermissions)
                        <div class="mb-3"><strong class="text-primary">{{ $group }}</strong><div class="row mt-2">
                            @foreach($groupPermissions as $permission)
                                <div class="col-md-4 custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input" id="role-permission-{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->id, $selectedPermissions))><label class="custom-control-label" for="role-permission-{{ $permission->id }}">{{ $permission->name }}</label></div>
                            @endforeach
                        </div></div>
                    @endforeach
                </div>@error('permissions')<span class="text-danger small">{{ $message }}</span>@enderror</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Role</button></div>
        </form>
    </div></div>
</div>
