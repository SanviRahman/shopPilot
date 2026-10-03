@php
    $isTrash = $isTrash ?? false;
@endphp
<div class="table-responsive">
    <table class="table table-hover table-striped mb-0">
        <thead><tr><th width="40"><input type="checkbox" id="contactMessageSelectAll"></th><th>Sender</th><th>Subject</th><th>Status</th><th>{{ $isTrash ? 'Deleted At' : 'Submitted At' }}</th><th width="155" class="text-right">Action</th></tr></thead>
        <tbody>
            @forelse($messages as $message)
                <tr class="{{ ! $isTrash && $message->status === 'new' ? 'font-weight-bold' : '' }}">
                    <td><input type="checkbox" value="{{ $message->id }}" data-contact-message-checkbox></td>
                    <td><div>{{ $message->name }}</div><small class="text-muted">{{ $message->email }}</small>@if($message->phone)<small class="text-muted d-block">{{ $message->phone }}</small>@endif</td>
                    <td><span title="{{ $message->subject }}">{{ \Illuminate\Support\Str::limit($message->subject, 55) }}</span></td>
                    <td>@php $statusClass = match($message->status) { 'new' => 'warning', 'read' => 'info', 'resolved' => 'success', default => 'secondary' }; @endphp<span class="badge badge-{{ $statusClass }} px-2 py-1">{{ ucfirst($message->status) }}</span></td>
                    <td>{{ ($isTrash ? $message->deleted_at : $message->created_at)?->format('d M Y, h:i A') }}</td>
                    <td class="text-right text-nowrap">
                        @if($isTrash)
                            @can('contact-messages.restore')<button type="button" class="btn btn-outline-success btn-xs btn-contact-message-action" data-url="{{ route('admin.contact-messages.restore', $message->id) }}" data-method="PATCH" data-confirm-title="Restore message?" data-confirm-text="This contact message will return to the inbox." title="Restore"><i class="fas fa-trash-restore"></i></button>@endcan
                            @can('contact-messages.force-delete')<button type="button" class="btn btn-outline-danger btn-xs btn-contact-message-action" data-url="{{ route('admin.contact-messages.force-delete', $message->id) }}" data-method="DELETE" data-confirm-title="Permanently delete?" data-confirm-text="This contact message cannot be recovered." title="Permanent Delete"><i class="fas fa-times"></i></button>@endcan
                        @else
                            @can('contact-messages.view')<button type="button" class="btn btn-outline-info btn-xs btn-view-contact-message" data-url="{{ route('admin.contact-messages.show', $message) }}" title="View"><i class="fas fa-eye"></i></button>@endcan
                            @can('contact-messages.update')<button type="button" class="btn btn-outline-primary btn-xs btn-edit-contact-message" data-url="{{ route('admin.contact-messages.edit', $message) }}" data-update-url="{{ route('admin.contact-messages.update', $message) }}" title="Update Status"><i class="fas fa-pen"></i></button>@endcan
                            @can('contact-messages.delete')<button type="button" class="btn btn-outline-danger btn-xs btn-contact-message-action" data-url="{{ route('admin.contact-messages.destroy', $message) }}" data-method="DELETE" data-confirm-title="Move to trash?" data-confirm-text="This message can be restored later." title="Move to Trash"><i class="fas fa-trash"></i></button>@endcan
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">{{ $isTrash ? 'Trash is empty.' : 'No contact messages found.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
