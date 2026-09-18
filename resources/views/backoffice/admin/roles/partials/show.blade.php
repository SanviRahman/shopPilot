@if(!empty($standalone))
    @extends('layouts.admin')
    @section('meta_title', 'Role Details')
    @section('page_content')
        <div class="card card-outline card-info"><div class="card-header"><h3 class="card-title">Role Details</h3></div><div class="card-body"><dl class="row mb-0"><dt class="col-sm-3">Role Name</dt><dd class="col-sm-9">{{ $role->name }}</dd><dt class="col-sm-3">Guard</dt><dd class="col-sm-9">{{ $role->guard_name }}</dd><dt class="col-sm-3">Permissions</dt><dd class="col-sm-9">{{ $role->permissions->pluck('name')->join(', ') ?: 'No permissions' }}</dd></dl></div></div>
    @endsection
@else
    <div class="modal fade" id="showRoleModal{{ $role->id }}" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-lg" role="document"><div class="modal-content"><div class="modal-header bg-info text-white"><h5 class="modal-title"><i class="fas fa-user-tag mr-1"></i>Role Details</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body"><dl class="row mb-0"><dt class="col-sm-3">Role Name</dt><dd class="col-sm-9">{{ $role->name }}</dd><dt class="col-sm-3">Guard</dt><dd class="col-sm-9">{{ $role->guard_name }}</dd><dt class="col-sm-3">Permissions</dt><dd class="col-sm-9">{{ $role->permissions->pluck('name')->join(', ') ?: 'No permissions' }}</dd></dl></div></div></div></div>
@endif
