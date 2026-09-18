@php
    $isEdit = $mode === 'edit' && $permission;
    $action = $isEdit ? route('admin.permissions.update', $permission) : route('admin.permissions.store');
@endphp
<div class="modal fade" id="permissionFormModal" tabindex="-1" role="dialog" aria-hidden="true" data-open-form="{{ $openForm ? 'true' : 'false' }}"><div class="modal-dialog" role="document"><div class="modal-content">
    <form action="{{ $action }}" method="POST" autocomplete="off">
        @csrf @if($isEdit) @method('PUT') @endif
        <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-key mr-1"></i>{{ $isEdit ? 'Update Permission' : 'Create Permission' }}</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">
            <div class="form-group"><label for="permission-name">Permission Name <span class="text-danger">*</span></label><input id="permission-name" name="name" value="{{ old('name', $permission?->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. products.view" required>@error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label for="permission-group-name">Group Name</label><input id="permission-group-name" name="group_name" value="{{ old('group_name', $permission?->group_name) }}" class="form-control" placeholder="e.g. Products"></div>
            <div class="form-group"><label for="permission-guard">Guard Name</label><input id="permission-guard" name="guard_name" value="admin" class="form-control" readonly></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Permission</button></div>
    </form>
</div></div></div>
