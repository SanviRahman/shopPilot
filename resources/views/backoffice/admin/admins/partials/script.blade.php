<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-select-all]').forEach(function (master) {
            master.addEventListener('change', function () {
                document.querySelectorAll(master.dataset.selectAll + ' [data-row-checkbox]').forEach(function (checkbox) {
                    checkbox.checked = master.checked;
                });
            });
        });

        document.querySelectorAll('[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.confirm(form.dataset.confirm)) {
                    event.preventDefault();
                }
            });
        });

        document.querySelectorAll('[data-confirm-bulk]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var action = form.querySelector('[name="action"]').value;
                var selected = document.querySelectorAll('[data-row-checkbox]:checked').length;

                if (!action || selected === 0) {
                    event.preventDefault();
                    window.alert('Select at least one admin and choose an action.');
                    return;
                }

                var labels = {
                    'delete': 'move selected admins to trash',
                    'restore': 'restore selected admins',
                    'force-delete': 'permanently delete selected admins'
                };

                if (!window.confirm('Are you sure you want to ' + labels[action] + '?')) {
                    event.preventDefault();
                    return;
                }

                form.querySelectorAll('input[name="admin_ids[]"]').forEach(function (input) {
                    input.remove();
                });
                document.querySelectorAll('[data-row-checkbox]:checked').forEach(function (checkbox) {
                    var clone = checkbox.cloneNode(true);
                    clone.checked = true;
                    form.appendChild(clone);
                });
            });
        });

        var modal = document.getElementById('adminFormModal');
        if (modal && modal.dataset.openForm === 'true' && window.jQuery) {
            window.jQuery(modal).modal('show');
        }
    });
</script>
