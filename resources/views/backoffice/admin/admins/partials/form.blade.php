@php
    $isEdit = $mode === 'edit' && $admin;
    $action = $isEdit ? route('admin.admins.update', $admin) : route('admin.admins.store');
@endphp

<div class="modal fade" id="adminFormModal" tabindex="-1" role="dialog" aria-labelledby="adminFormModalLabel" aria-hidden="true" data-open-form="{{ $openForm ? 'true' : 'false' }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ $action }}" method="POST" autocomplete="off">
                @csrf
                @if($isEdit) @method('PUT') @endif
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="adminFormModalLabel">
                        <i class="fas fa-user-shield mr-1"></i>{{ $isEdit ? 'Update Admin' : 'Create Admin' }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="admin-name">Name <span class="text-danger">*</span></label>
                            <input type="text" id="admin-name" name="name" value="{{ old('name', $admin?->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="admin-email">Email <span class="text-danger">*</span></label>
                            <input type="email" id="admin-email" name="email" value="{{ old('email', $admin?->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="admin-password">Password @unless($isEdit)<span class="text-danger">*</span>@endunless</label>
                            <input type="password" id="admin-password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $isEdit ? '' : 'required' }}>
                            @if($isEdit)<small class="form-text text-muted">Leave blank to keep the current password.</small>@endif
                            @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="admin-password-confirmation">Confirm Password @unless($isEdit)<span class="text-danger">*</span>@endunless</label>
                            <input type="password" id="admin-password-confirmation" name="password_confirmation" class="form-control" {{ $isEdit ? '' : 'required' }}>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="admin-status">Status <span class="text-danger">*</span></label>
                            <select id="admin-status" name="status" class="form-control" required>
                                @foreach(['active', 'inactive'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $admin?->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-12">
                            <label>Admin Roles <span class="text-danger">*</span></label>
                            <div class="row border rounded p-2 ml-0 mr-0">
                                @php($selectedRoles = old('roles', $admin?->roles?->modelKeys() ?? []))
                                @foreach($roles as $role)
                                    <div class="col-md-4 custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="role-{{ $role->id }}" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, $selectedRoles))>
                                        <label class="custom-control-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('roles')<span class="text-danger small">{{ $message }}</span>@enderror
                            @error('roles.*')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>
