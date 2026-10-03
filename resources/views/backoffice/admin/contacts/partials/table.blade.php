@php
    $isTrash = $isTrash ?? false;
@endphp

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="thead-light">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Map</th>
                @if($isTrash)
                    <th>Deleted At</th>
                @else
                    <th>Status</th>
                @endif
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contacts as $contact)
                <tr>
                    <td><strong class="text-dark">{{ $contact->name }}</strong><br><small class="text-muted">#{{ $contact->id }}</small></td>
                    <td><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></td>
                    <td><a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a></td>
                    <td><a href="{{ $contact->map_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-light btn-sm border"><i class="fas fa-map-marker-alt text-danger mr-1"></i> Open Map</a></td>
                    @if($isTrash)
                        <td><small class="text-muted">{{ $contact->deleted_at?->format('d M Y, h:i A') }}</small></td>
                    @else
                        <td><span class="badge badge-{{ $contact->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($contact->status) }}</span></td>
                    @endif
                    <td class="text-right text-nowrap">
                        @if($isTrash)
                            @can('contacts.restore')
                                <button type="button" class="btn btn-outline-success btn-sm btn-contact-action" data-url="{{ route('admin.contacts.restore', $contact->id) }}" data-method="PATCH" data-confirm-title="Restore contact?" data-confirm-text="This contact will return to the active list." title="Restore"><i class="fas fa-trash-restore"></i></button>
                            @endcan
                            @can('contacts.force-delete')
                                <button type="button" class="btn btn-outline-danger btn-sm btn-contact-action" data-url="{{ route('admin.contacts.force-delete', $contact->id) }}" data-method="DELETE" data-confirm-title="Permanently delete?" data-confirm-text="This action cannot be undone." title="Permanent Delete"><i class="fas fa-times"></i></button>
                            @endcan
                        @else
                            <button type="button" class="btn btn-default btn-sm btn-view-contact" data-url="{{ route('admin.contacts.show', $contact) }}" title="View"><i class="fas fa-eye text-info"></i></button>
                            @can('contacts.update')
                                <button type="button" class="btn btn-default btn-sm btn-edit-contact" data-url="{{ route('admin.contacts.edit', $contact) }}" data-update-url="{{ route('admin.contacts.update', $contact) }}" title="Edit"><i class="fas fa-pen text-primary"></i></button>
                            @endcan
                            @can('contacts.delete')
                                <button type="button" class="btn btn-default btn-sm btn-contact-action" data-url="{{ route('admin.contacts.destroy', $contact) }}" data-method="DELETE" data-confirm-title="Move to trash?" data-confirm-text="The contact can be restored later." title="Move to Trash"><i class="fas fa-trash text-danger"></i></button>
                            @endcan
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">{{ $isTrash ? 'Trash is empty.' : 'No contact information found.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
