<script>
$(function () {
    const fetchUrl = '{{ $fetchUrl }}';

    function showToast(message, type = 'success') {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            Toast.fire({ icon: type, title: message });
        } else {
            alert(message);
        }
    }

    function reloadTable(url = fetchUrl) {
        $('#tableOverlay').removeClass('d-none');
        const formData = $('#filterForm').serialize();

        $.ajax({
            url: url,
            method: 'GET',
            data: formData,
            dataType: 'json',
            success: function (res) {
                $('#tableContainer').html(res.html);
                $('#paginationContainer').html(res.pagination);
            },
            error: function () {
                showToast('Failed to load histories. Try again.', 'error');
            },
            complete: function () {
                $('#tableOverlay').addClass('d-none');
            }
        });
    }

    let searchTimeout = null;
    $('#search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => reloadTable(), 400);
    });

    $('#order_id, #status').on('change', function () {
        reloadTable();
    });

    $('#btnResetFilter').on('click', function () {
        $('#filterForm')[0].reset();
        reloadTable();
    });

    $(document).on('click', '#paginationContainer a.page-link', function (e) {
        e.preventDefault();
        const pageUrl = $(this).attr('href');
        if (pageUrl) reloadTable(pageUrl);
    });

    $(document).on('change', '[data-select-all]', function () {
        const target = $(this).data('selectAll');
        $(`${target} [data-row-checkbox]`).prop('checked', $(this).is(':checked'));
    });

    function clearFormErrors() {
        $('.invalid-feedback').text('');
        $('.form-control, .custom-select').removeClass('is-invalid');
    }

    $('#btnCreateHistory').on('click', function (e) {
        e.preventDefault();
        clearFormErrors();
        $('#historyAjaxForm')[0].reset();
        $('#historyFormModal').modal('show');
    });

    $('#historyAjaxForm').on('submit', function (e) {
        e.preventDefault();
        clearFormErrors();

        const form = $(this);
        const submitBtn = $('#btnSubmitHistoryForm');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#historyFormModal').modal('hide');
                    showToast(res.message, 'success');
                    reloadTable();
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};
                    $.each(errors, function (key, messages) {
                        $(`#err-${key}`).text(messages[0]);
                        $(`[name="${key}"]`).addClass('is-invalid');
                    });
                } else {
                    showToast(xhr.responseJSON?.message || 'Something went wrong.', 'error');
                }
            },
            complete: function () {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Add Note');
            }
        });
    });

    $(document).on('click', '.btn-view-history', function (e) {
        e.preventDefault();
        const historyId = $(this).data('id');

        $.ajax({
            url: `/admin/order-histories/${historyId}`,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    const h = res.history;
                    $('#show-order-number').text(h.order_number);
                    $('#show-buyer-name').text(h.buyer_name);
                    $('#show-actor-badge').html(h.actor_badge);
                    $('#show-transition-badge').html(h.transition_badge);
                    $('#show-created-at').text(h.created_at);
                    $('#show-note').text(h.note || '—');
                    $('#historyShowModal').modal('show');
                }
            },
            error: function () {
                showToast('Could not load history details.', 'error');
            }
        });
    });

    let pendingAction = null;

    $(document).on('click', '.btn-action', function (e) {
        e.preventDefault();
        const btn = $(this);
        pendingAction = {
            url: btn.data('url'),
            method: btn.data('method'),
            data: { _token: '{{ csrf_token() }}' }
        };

        $('#confirmModalTitle').text(btn.data('confirm-title') || 'Are you sure?');
        $('#confirmModalText').text(btn.data('confirm-text') || 'Confirm this recovery action.');
        $('#confirmModal').modal('show');
    });

    $('#confirmModalBtn').on('click', function () {
        if (!pendingAction) return;

        const confirmBtn = $(this);
        confirmBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

        $.ajax({
            url: pendingAction.url,
            method: pendingAction.method,
            data: pendingAction.data,
            dataType: 'json',
            success: function (res) {
                $('#confirmModal').modal('hide');
                showToast(res.message, 'success');
                reloadTable();
            },
            error: function (xhr) {
                $('#confirmModal').modal('hide');
                showToast(xhr.responseJSON?.message || 'Operation failed.', 'error');
            },
            complete: function () {
                confirmBtn.prop('disabled', false).text('Confirm');
                pendingAction = null;
            }
        });
    });

    $('#bulkActionForm').on('submit', function (e) {
        e.preventDefault();
        const action = $(this).find('[name="action"]').val();
        const selected = $('[data-row-checkbox]:checked').map(function () {
            return $(this).val();
        }).get();

        if (!action || selected.length === 0) {
            showToast('Please select at least one record and choose an action.', 'error');
            return;
        }

        const labels = {
            'delete': 'move selected histories to trash',
            'restore': 'restore selected histories'
        };

        pendingAction = {
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                history_ids: selected
            }
        };

        $('#confirmModalTitle').text('Confirm Bulk Action');
        $('#confirmModalText').text(`Are you sure you want to ${labels[action] || 'process'}?`);
        $('#confirmModal').modal('show');
    });
});
</script>
