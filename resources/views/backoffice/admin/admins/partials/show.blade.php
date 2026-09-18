@if(!empty($standalone))
    @extends('layouts.admin')
    @section('meta_title', 'Admin Details')
    @section('page_content')
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Admin Details</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $admin->name }}</dd>
                    <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $admin->email }}</dd>
                    <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ ucfirst($admin->status) }}</dd>
                    <dt class="col-sm-3">Roles</dt><dd class="col-sm-9">{{ $admin->roles->pluck('name')->join(', ') ?: 'No role' }}</dd>
                    <dt class="col-sm-3">Created At</dt><dd class="col-sm-9">{{ optional($admin->created_at)->format('d M Y, h:i A') }}</dd>
                </dl>
            </div>
        </div>
    @endsection
@else
    <div class="modal fade" id="showAdminModal{{ $admin->id }}" tabindex="-1" role="dialog" aria-labelledby="showAdminModalLabel{{ $admin->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="showAdminModalLabel{{ $admin->id }}"><i class="fas fa-user mr-1"></i>Admin Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Name</dt><dd class="col-sm-8">{{ $admin->name }}</dd>
                        <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $admin->email }}</dd>
                        <dt class="col-sm-4">Status</dt><dd class="col-sm-8">{{ ucfirst($admin->status) }}</dd>
                        <dt class="col-sm-4">Roles</dt><dd class="col-sm-8">{{ $admin->roles->pluck('name')->join(', ') ?: 'No role' }}</dd>
                        <dt class="col-sm-4">Created</dt><dd class="col-sm-8">{{ optional($admin->created_at)->format('d M Y, h:i A') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endif
