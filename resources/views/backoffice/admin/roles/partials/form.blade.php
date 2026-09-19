@php
    $permissionGroups = isset($permissions) ? $permissions->groupBy(fn ($p) => $p->group_name ?: 'General Permissions') : collect();
@endphp

<div class="modal fade" id="roleFormModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="roleFormModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="roleAjaxForm" action="" method="POST" autocomplete="off">
                @csrf
                <input type="hidden" name="_method" id="roleFormMethod" value="POST">
                
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="roleFormModalTitle">
                        <i class="fas fa-user-tag text-primary mr-2"></i><span>Create Role</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body p-4">

                    <div class="form-group mb-4">
                        <label for="role-name" class="font-weight-600">Role Name (Slug format) <span class="text-danger">*</span></label>
                        <input type="text" id="role-name" name="name" class="form-control form-control-sm" placeholder="e.g. branch_manager, staff_admin" required>
                        <small class="text-muted">Allowed lowercase letters, numbers, hyphens and dots (e.g., manager, admin.lead).</small>
                        <div class="invalid-feedback d-block" id="err-name"></div>
                    </div>

                    <div class="form-group mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-600 mb-0">Assign Permissions</label>
                            <div>
                                <button type="button" class="btn btn-xs btn-outline-primary" id="btnCheckAllPermissions">Select All</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary ml-1" id="btnUncheckAllPermissions">Deselect All</button>
                            </div>
                        </div>

                        <div class="border rounded p-3 bg-light" style="max-height: 380px; overflow-y: auto;">
                            @foreach($permissionGroups as $group => $groupPermissions)
                                <div class="card card-outline card-secondary mb-3 shadow-none border">
                                    <div class="card-header bg-white py-1 px-3 d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold text-dark small text-uppercase"><i class="fas fa-layer-group text-primary mr-1"></i> {{ $group }}</span>
                                        <button type="button" class="btn btn-link btn-xs text-muted p-0 toggle-group-perms" data-group="grp-{{ Str::slug($group) }}">Toggle</button>
                                    </div>
                                    <div class="card-body p-2 bg-white">
                                        <div class="row">
                                            @foreach($groupPermissions as $permission)
                                                <div class="col-md-4 col-sm-6 mb-1">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input perm-checkbox grp-{{ Str::slug($group) }}" id="perm-{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}">
                                                        <label class="custom-control-label font-weight-normal text-dark small" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="invalid-feedback d-block" id="err-permissions"></div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-light btn-sm border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitRoleForm">
                        <i class="fas fa-save mr-1"></i> Save Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>