<script>
    window.mediaPicker = window.mediaPicker || {};

    (function ($) {
        const pickerState = { target: null, url: null, search: '' };

        function pickerFor(inputId) {
            const input = document.getElementById(inputId);
            return input ? input.closest('[data-media-picker]') : null;
        }

        function setPickerPreview(inputId, url, mediaId, sourceName) {
            const input = document.getElementById(inputId);
            const picker = pickerFor(inputId);
            if (!input || !picker) return;

            const container = picker.querySelector('[data-media-preview-container]');
            const preview = picker.querySelector('[data-media-preview]');
            const label = picker.querySelector('[data-media-label]');
            const removeField = picker.querySelector('[data-media-remove-field]');
            const mediaIdField = picker.querySelector('[data-media-id-field]');
            const sourceLabel = picker.querySelector('[data-media-source-label]');

            input.value = '';
            if (mediaIdField) mediaIdField.value = mediaId || '';
            if (removeField) removeField.value = '0';
            if (label) label.textContent = mediaId ? 'Using existing media' : (url ? 'Current image' : (input.dataset.chooseLabel || label.textContent));
            if (sourceLabel) sourceLabel.textContent = sourceName ? (mediaId ? 'Existing media: ' : 'Current image: ') + sourceName : '';

            if (url) {
                preview.src = url;
                container.classList.remove('d-none');
            } else {
                preview.removeAttribute('src');
                container.classList.add('d-none');
            }
        }

        window.mediaPicker.setPreview = function (inputId, url, sourceName) {
            setPickerPreview(inputId, url, '', sourceName || '');
        };

        window.mediaPicker.setExisting = function (inputId, media) {
            if (typeof media === 'string') {
                setPickerPreview(inputId, arguments[1], arguments[2], arguments[3]);
                return;
            }
            setPickerPreview(inputId, media?.url || null, media?.id || '', media?.name || media?.file_name || '');
        };

        window.mediaPicker.reset = function (scope) {
            $(scope || document).find('[data-media-picker]').each(function () {
                const picker = this;
                const input = picker.querySelector('[data-media-input]');
                const container = picker.querySelector('[data-media-preview-container]');
                const preview = picker.querySelector('[data-media-preview]');
                const label = picker.querySelector('[data-media-label]');
                const removeField = picker.querySelector('[data-media-remove-field]');
                const mediaIdField = picker.querySelector('[data-media-id-field]');
                const sourceLabel = picker.querySelector('[data-media-source-label]');

                if (input) input.value = '';
                if (mediaIdField) mediaIdField.value = '';
                if (removeField) removeField.value = '0';
                if (preview) preview.removeAttribute('src');
                if (container) container.classList.add('d-none');
                if (sourceLabel) sourceLabel.textContent = '';
                if (label) label.textContent = input?.dataset.chooseLabel || 'Choose image file...';
            });
        };

        function escapeHtml(value) {
            return $('<div>').text(value || '').html();
        }

        function renderPickerItems(items) {
            const grid = $('#mediaPickerGrid');
            grid.empty();
            $('#mediaPickerEmpty').toggleClass('d-none', items.length !== 0);

            items.forEach(function (media) {
                grid.append(`
                    <div class="col-6 col-md-3 mb-3">
                        <button type="button" class="card media-picker-card w-100 h-100 text-left p-0 border bg-white" data-picker-media='${escapeHtml(JSON.stringify(media))}'>
                            <img src="${escapeHtml(media.thumb_url || media.url)}" class="card-img-top" style="height:130px;object-fit:cover;" alt="${escapeHtml(media.name)}">
                            <div class="card-body p-2">
                                <div class="font-weight-bold small text-truncate" title="${escapeHtml(media.name)}">${escapeHtml(media.name)}</div>
                                <small class="text-muted d-block text-truncate">${escapeHtml(media.collection_name)}</small>
                                <small class="text-muted">${escapeHtml(media.file_name)}</small>
                            </div>
                        </button>
                    </div>
                `);
            });
        }

        function loadPickerItems() {
            if (!pickerState.url) return;
            $('#mediaPickerLoading').removeClass('d-none');
            $('#mediaPickerGrid, #mediaPickerEmpty').addClass('d-none');

            $.getJSON(pickerState.url, { search: pickerState.search })
                .done(function (response) {
                    renderPickerItems(response.data || []);
                    $('#mediaPickerGrid, #mediaPickerEmpty').removeClass('d-none');
                })
                .fail(function (xhr) {
                    if (typeof showAlert === 'function') showAlert(xhr.responseJSON?.message || 'Unable to load media library.', 'error');
                })
                .always(function () {
                    $('#mediaPickerLoading').addClass('d-none');
                });
        }

        $(document).on('change', '[data-media-input]', function () {
            const input = this;
            const picker = input.closest('[data-media-picker]');
            const file = input.files && input.files[0];
            if (!picker || !file) return;

            const label = picker.querySelector('[data-media-label]');
            const preview = picker.querySelector('[data-media-preview]');
            const container = picker.querySelector('[data-media-preview-container]');
            const removeField = picker.querySelector('[data-media-remove-field]');
            const mediaIdField = picker.querySelector('[data-media-id-field]');
            const sourceLabel = picker.querySelector('[data-media-source-label]');

            if (label) label.textContent = file.name;
            if (removeField) removeField.value = '0';
            if (mediaIdField) mediaIdField.value = '';
            if (sourceLabel) sourceLabel.textContent = 'New image selected';
            if (preview) preview.src = URL.createObjectURL(file);
            if (container) container.classList.remove('d-none');
        });

        $(document).on('click', '[data-media-remove]', function () {
            const picker = this.closest('[data-media-picker]');
            if (!picker) return;

            const input = picker.querySelector('[data-media-input]');
            const label = picker.querySelector('[data-media-label]');
            const container = picker.querySelector('[data-media-preview-container]');
            const preview = picker.querySelector('[data-media-preview]');
            const removeField = picker.querySelector('[data-media-remove-field]');
            const mediaIdField = picker.querySelector('[data-media-id-field]');
            const sourceLabel = picker.querySelector('[data-media-source-label]');

            if (input) input.value = '';
            if (label) label.textContent = input?.dataset.chooseLabel || 'Choose image file...';
            if (preview) preview.removeAttribute('src');
            if (container) container.classList.add('d-none');
            if (removeField) removeField.value = '1';
            if (mediaIdField) mediaIdField.value = '';
            if (sourceLabel) sourceLabel.textContent = '';
        });

        $(document).on('click', '[data-media-browse]', function () {
            pickerState.target = this.dataset.mediaTarget;
            pickerState.url = this.dataset.pickerUrl;
            pickerState.search = '';
            $('#mediaPickerSearch').val('');
            $('#mediaPickerModal').modal('show');
            loadPickerItems();
        });

        $('#mediaPickerRefresh').on('click', loadPickerItems);
        $('#mediaPickerSearch').on('input', function () {
            pickerState.search = this.value;
            clearTimeout(window.mediaPickerSearchTimer);
            window.mediaPickerSearchTimer = setTimeout(loadPickerItems, 350);
        });

        $(document).on('click', '[data-picker-media]', function () {
            if (!pickerState.target) return;
            const media = JSON.parse($(this).attr('data-picker-media'));
            window.mediaPicker.setExisting(pickerState.target, media);
            $('#mediaPickerModal').modal('hide');
        });
    })(jQuery);
</script>
