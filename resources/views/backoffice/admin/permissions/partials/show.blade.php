<div class="modal fade" id="showPermissionModal{{ $permission->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-key mr-1"></i>Permission Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">ID</dt><dd class="col-sm-8">#{{ $permission->id }}</dd>
                    <dt class="col-sm-4">Permission</dt><dd class="col-sm-8">{{ $permission->name }}</dd>
                    <dt class="col-sm-4">Guard</dt><dd class="col-sm-8">{{ $permission->guard_name }}</dd>
                    <dt class="col-sm-4">Group</dt><dd class="col-sm-8">{{ $permission->group_name ?: 'Other' }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
