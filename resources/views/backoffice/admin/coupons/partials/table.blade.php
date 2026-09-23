@php
    $isTrash = $isTrash ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover table-striped mb-0">
        <thead>
            <tr>
                <th width="40">
                    <input type="checkbox" id="couponSelectAll">
                </th>
                <th>Code</th>
                <th>Type</th>
                <th>Value</th>
                <th>Min Order</th>
                <th>Expires At</th>
                <th>Status</th>
                <th width="140" class="text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($coupons as $coupon)
                <tr>
                    <td>
                        <input type="checkbox" name="coupon_ids[]" value="{{ $coupon->id }}" class="coupon-checkbox" data-coupon-checkbox>
                    </td>
                    <td>
                        <strong>{{ $coupon->code }}</strong>
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ ucfirst($coupon->type) }}</span>
                    </td>
                    <td>
                        {{ $coupon->type === 'fixed' ? '৳ ' . number_format($coupon->value, 2) : $coupon->value . '%' }}
                    </td>
                    <td>
                        {{ $coupon->min_order_amount ? '৳ ' . number_format($coupon->min_order_amount, 2) : 'N/A' }}
                    </td>
                    <td>
                        {{ optional($coupon->expires_at)->format('d M Y, h:i A') ?? 'No Expiry' }}
                    </td>
                    <td>
                        @php
                            $badgeClass = match($coupon->status) {
                                'active' => 'badge-success',
                                'inactive' => 'badge-warning',
                                'expired' => 'badge-danger',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($coupon->status) }}</span>
                    </td>
                    <td class="text-right">
                        @if($isTrash)
                            <div class="btn-group btn-group-sm">
                                @can('coupons.restore')
                                    <form method="POST" action="{{ route('admin.coupons.restore', $coupon->id) }}" class="d-inline coupon-action-form" data-confirm-title="Restore Coupon?" data-confirm-text="This coupon will be restored.">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-default text-success btn-sm px-2" title="Restore">
                                            <i class="fas fa-trash-restore"></i>
                                        </button>
                                    </form>
                                @endcan

                                @can('coupons.force-delete')
                                    <form method="POST" action="{{ route('admin.coupons.force-delete', $coupon->id) }}" class="d-inline coupon-action-form" data-confirm-title="Permanently Delete?" data-confirm-text="This item cannot be recovered.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-default text-danger btn-sm px-2" title="Permanent Delete">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        @else
                            <div class="btn-group btn-group-sm">
                                @can('coupons.view')
                                    <button type="button" 
                                        class="btn btn-default text-info btn-sm px-2 btn-show-coupon" 
                                        data-url="{{ route('admin.coupons.show', $coupon->id) }}"
                                        title="View Coupon">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endcan

                                @can('coupons.update')
                                    <button type="button" 
                                        class="btn btn-default text-primary btn-sm px-2 btn-edit-coupon" 
                                        data-url="{{ route('admin.coupons.edit', $coupon->id) }}"
                                        title="Edit Coupon">
                                        <i class="fas fa-pen text-primary"></i>
                                    </button>
                                @endcan

                                @can('coupons.delete')
                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon->id) }}" class="d-inline coupon-action-form" data-confirm-title="Move to Trash?" data-confirm-text="This coupon can be restored later.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-default text-danger btn-sm px-2" title="Delete">
                                             <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No coupons found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>