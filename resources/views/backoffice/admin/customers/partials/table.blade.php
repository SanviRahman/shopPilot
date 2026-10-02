@php($isTrash = $isTrash ?? false)
<div class="table-responsive">
    <table id="{{ $isTrash ? 'customer-trash-list' : 'customers-list' }}" class="table table-hover mb-0">
        <thead class="thead-light"><tr><th width="40" class="text-center"><input type="checkbox" data-select-all="#{{ $isTrash ? 'customer-trash-list' : 'customers-list' }}"></th><th>Customer</th><th>Orders</th><th>{{ $isTrash ? 'Deleted At' : 'Status' }}</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
        @forelse($customers as $customer)
            <tr>
                <td class="text-center"><input type="checkbox" value="{{ $customer->id }}" data-row-checkbox></td>
                <td><div class="d-flex align-items-center"><div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width:38px;height:38px">{{ strtoupper(substr($customer->name,0,1)) }}</div><div><div class="font-weight-bold">{{ $customer->name }}</div><small class="text-muted">{{ $customer->email }}</small></div></div></td>
                <td class="align-middle"><span class="badge badge-info px-2 py-1">{{ $customer->orders_count }} order(s)</span></td>
                <td class="align-middle">
                    @if($isTrash)<small class="text-muted">{{ optional($customer->deleted_at)->format('d M Y, h:i A') }}</small>
                    @elseif($customer->isActive())<span class="badge badge-success">Active</span>@else<span class="badge badge-secondary">Inactive</span>@endif
                </td>
                <td class="text-right align-middle text-nowrap">
                    @if($isTrash)
                        @can('customers.restore')<button class="btn btn-outline-success btn-sm btn-customer-action" data-url="{{ route('admin.customers.restore',$customer->id) }}" data-method="PATCH" data-title="Restore Customer?" data-text="Restore {{ $customer->name }} to the active customer list?"><i class="fas fa-trash-restore"></i></button>@endcan
                        @can('customers.force-delete')<button class="btn btn-outline-danger btn-sm btn-customer-action" data-url="{{ route('admin.customers.force-delete',$customer->id) }}" data-method="DELETE" data-title="Permanently Delete?" data-text="This permanently deletes the customer account. Existing orders keep their historical buyer data."><i class="fas fa-times"></i></button>@endcan
                    @else
                        <div class="btn-group btn-group-sm">
                            @can('customers.view')<button class="btn btn-default btn-view-customer" data-id="{{ $customer->id }}"><i class="fas fa-eye text-info"></i></button>@endcan
                            @can('customers.update')<button class="btn btn-default btn-edit-customer" data-id="{{ $customer->id }}"><i class="fas fa-pen text-primary"></i></button>@endcan
                            @can('customers.delete')<button class="btn btn-default btn-customer-action" data-url="{{ route('admin.customers.destroy',$customer) }}" data-method="DELETE" data-title="Move to Trash?" data-text="Customer {{ $customer->name }} will be soft deleted and cannot login."><i class="fas fa-trash text-danger"></i></button>@endcan
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-5"><i class="fas fa-users fa-3x text-light mb-3 d-block"></i>{{ $isTrash ? 'Customer trash is empty.' : 'No customers found.' }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
