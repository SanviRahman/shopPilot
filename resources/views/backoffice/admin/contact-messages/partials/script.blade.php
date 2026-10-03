<script>
$(function () {
    const fetchUrl = @json($fetchUrl);
    const isTrashPage = @json((bool) ($isTrashPage ?? false));
    const csrfToken = @json(csrf_token());
    let searchTimeout = null;

    function reloadContactMessageTable(url = fetchUrl) {
        $('#contactMessageTableOverlay').removeClass('d-none');
        $.ajax({url: url, method: 'GET', data: $('#contactMessageFilterForm').serialize(), dataType: 'json', success: function (res) { $('#contactMessageTableContainer').html(res.html || ''); $('#contactMessagePaginationContainer').html(res.pagination || ''); }, error: function (xhr) { showAlert(xhr.responseJSON?.message || 'Failed to refresh contact messages.', 'error'); }, complete: function () { $('#contactMessageTableOverlay').addClass('d-none'); $('#contactMessageSelectAll').prop('checked', false); }});
    }

    function statusClass(status) {
        if (status === 'resolved') return 'badge-success';
        if (status === 'read') return 'badge-info';
        if (status === 'new') return 'badge-warning';
        return 'badge-secondary';
    }

    function populateShow(message) {
        $('#showContactMessageName').text(message.name || '—');
        $('#showContactMessageEmail').attr('href', message.email ? `mailto:${message.email}` : '#').text(message.email || '—');
        $('#showContactMessagePhone').attr('href', message.phone ? `tel:${message.phone}` : '#').text(message.phone || '—');
        $('#showContactMessageSubject').text(message.subject || '—');
        $('#showContactMessageBody').text(message.message || '—');
        $('#showContactMessageStatus').removeClass('badge-warning badge-info badge-success badge-secondary').addClass(statusClass(message.status)).text(message.status ? message.status.charAt(0).toUpperCase() + message.status.slice(1) : '—');
        $('#showContactMessageSubmitted').text(message.created_at ? `Submitted ${message.created_at}` : '');
        $('#showContactMessageRead').text(message.read_at || 'Not read yet');
        $('#showContactMessageResolved').text(message.resolved_at || 'Not resolved yet');
        $('#showContactMessageIp').text(message.ip_address || '—');
        $('#showContactMessageAgent').text(message.user_agent || '—');
    }

    $('#contactMessageFilterForm input[name="search"]').on('input', function () { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => reloadContactMessageTable(), 350); });
    $('#contactMessageFilterForm select[name="status"]').on('change', function () { reloadContactMessageTable(); });
    $('#btnResetContactMessageFilter').on('click', function () { $('#contactMessageFilterForm')[0]?.reset(); reloadContactMessageTable(); });
    $(document).on('click', '#contactMessagePaginationContainer a.page-link', function (event) { event.preventDefault(); const url = $(this).attr('href'); if (url) reloadContactMessageTable(url); });
    $(document).on('change', '#contactMessageSelectAll', function () { $('[data-contact-message-checkbox]').prop('checked', this.checked); });

    $(document).on('click', '.btn-view-contact-message', function (event) {
        event.preventDefault();
        $.ajax({url: $(this).data('url'), method: 'GET', dataType: 'json', success: function (res) { if (!res.success) return; populateShow(res.message); $('#contactMessageShowModal').modal('show'); }, error: function (xhr) { showAlert(xhr.responseJSON?.message || 'Could not load contact message.', 'error'); }});
    });

    $(document).on('click', '.btn-edit-contact-message', function (event) {
        event.preventDefault();
        const button = $(this);
        $.ajax({url: button.data('url'), method: 'GET', dataType: 'json', success: function (res) { if (!res.success) return; const message = res.message; $('#contactMessageStatusForm').attr('action', button.data('update-url')); $('#contact-message-status').val(message.status || 'new'); $('#contactMessageStatusSubject').text(message.subject || 'Contact message'); $('#contactMessageStatusSender').text(`${message.name || ''}${message.email ? ' · ' + message.email : ''}`); $('#contactMessageStatusModal').modal('show'); }, error: function (xhr) { showAlert(xhr.responseJSON?.message || 'Could not load contact message for editing.', 'error'); }});
    });

    $(document).off('submit.contactMessageStatus', '#contactMessageStatusForm').on('submit.contactMessageStatus', '#contactMessageStatusForm', function (event) {
        event.preventDefault();
        const form = $(this);
        const button = $('#btnSubmitContactMessageStatus');
        const original = button.html();
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
        $.ajax({url: form.attr('action'), method: 'POST', data: form.serialize(), dataType: 'json', success: function (res) { $('#contactMessageStatusModal').modal('hide'); showAlert(res.message || 'Status updated.', 'success'); reloadContactMessageTable(); }, error: function (xhr) { showAlert(xhr.responseJSON?.message || 'Could not update message status.', 'error'); }, complete: function () { button.prop('disabled', false).html(original); }});
    });

    function executeAction(button) {
        $.ajax({url: button.data('url'), method: button.data('method') || 'POST', data: {_token: csrfToken}, dataType: 'json', success: function (res) { showAlert(res.message || 'Operation completed successfully.', 'success'); reloadContactMessageTable(); }, error: function (xhr) { showAlert(xhr.responseJSON?.message || 'Operation failed.', 'error'); }});
    }

    $(document).on('click', '.btn-contact-message-action', function (event) {
        event.preventDefault();
        const button = $(this);
        const execute = () => executeAction(button);
        if (!window.Swal || typeof window.Swal.fire !== 'function') { if (window.confirm(button.data('confirm-text') || 'Continue?')) execute(); return; }
        window.Swal.fire({title: button.data('confirm-title') || 'Are you sure?', text: button.data('confirm-text') || 'Please confirm this action.', type: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Yes, continue', cancelButtonText: 'Cancel'}).then(function (result) { if (result.value || result.isConfirmed) execute(); });
    });

    $(document).off('submit.contactMessageBulk', '#contactMessageBulkForm').on('submit.contactMessageBulk', '#contactMessageBulkForm', function (event) {
        event.preventDefault();
        const form = $(this);
        const action = form.find('[name="action"]').val();
        const ids = $('[data-contact-message-checkbox]:checked').map(function () { return this.value; }).get();
        if (!action || !ids.length) { showAlert('Please select messages and a bulk action.', 'error'); return; }
        const run = function () { $.ajax({url: form.attr('action'), method: 'POST', data: form.serialize() + '&' + $.param({message_ids: ids}), dataType: 'json', success: function (res) { showAlert(res.message || 'Messages processed.', 'success'); reloadContactMessageTable(); }, error: function (xhr) { showAlert(xhr.responseJSON?.message || 'Bulk action failed.', 'error'); }}); };
        if (!window.Swal || typeof window.Swal.fire !== 'function') { if (window.confirm('Process selected contact messages?')) run(); return; }
        window.Swal.fire({title: 'Confirm bulk action', text: `${ids.length} contact message(s) will be processed.`, type: action === 'force-delete' ? 'warning' : 'question', showCancelButton: true, confirmButtonText: 'Yes, continue'}).then(function (result) { if (result.value || result.isConfirmed) run(); });
    });

    if (!isTrashPage) {
        const query = new URLSearchParams(window.location.search);
        const showId = Number(query.get('show') || 0);
        const editId = Number(query.get('edit') || 0);
        if (showId > 0) $(`.btn-view-contact-message[data-url$="/${showId}"]`).first().trigger('click');
        if (editId > 0) $(`.btn-edit-contact-message[data-update-url$="/${editId}"]`).first().trigger('click');
    }
});
</script>
