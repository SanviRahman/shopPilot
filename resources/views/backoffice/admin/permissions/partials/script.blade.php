<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-select-all]').forEach(function (master) { master.addEventListener('change', function () { document.querySelectorAll(master.dataset.selectAll + ' [data-row-checkbox]').forEach(function (checkbox) { checkbox.checked = master.checked; }); }); });
    document.querySelectorAll('[data-confirm]').forEach(function (form) { form.addEventListener('submit', function (event) { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }); });
    document.querySelectorAll('[data-confirm-permission-bulk]').forEach(function (form) { form.addEventListener('submit', function (event) { var action = form.querySelector('[name="action"]').value; var selected = document.querySelectorAll('[data-row-checkbox]:checked').length; if (!action || !selected) { event.preventDefault(); window.alert('Select at least one permission and choose an action.'); return; } if (!window.confirm('Are you sure you want to perform this action?')) { event.preventDefault(); return; } document.querySelectorAll('[data-row-checkbox]:checked').forEach(function (checkbox) { var clone = checkbox.cloneNode(true); clone.name = 'permission_ids[]'; form.appendChild(clone); }); }); });
    var modal = document.getElementById('permissionFormModal'); if (modal && modal.dataset.openForm === 'true' && window.jQuery) window.jQuery(modal).modal('show');
});
</script>
