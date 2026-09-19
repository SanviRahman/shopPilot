<script>
    window.mediaPicker = window.mediaPicker || {};

    (function ($) {
        'use strict';

        const pickerState = {
            target: null,
            url: null,
            search: '',
            collection: '',
            disk: '',
            items: new Map(),
            selectedIds: new Set(),
        };

        function pickerFor(inputId) {
            const input = document.getElementById(inputId);

            return input
                ? input.closest('[data-media-picker]')
                : null;
        }

        function setPickerPreview(
            inputId,
            url,
            mediaId,
            sourceName
        ) {
            const input = document.getElementById(inputId);
            const picker = pickerFor(inputId);

            if (!input || !picker) {
                return;
            }

            const container = picker.querySelector(
                '[data-media-preview-container]'
            );
            const preview = picker.querySelector(
                '[data-media-preview]'
            );
            const label = picker.querySelector('[data-media-label]');
            const removeField = picker.querySelector(
                '[data-media-remove-field]'
            );
            const mediaIdField = picker.querySelector(
                '[data-media-id-field]'
            );
            const sourceLabel = picker.querySelector(
                '[data-media-source-label]'
            );

            input.value = '';

            if (mediaIdField) {
                mediaIdField.value = mediaId || '';
            }

            if (removeField) {
                removeField.value = '0';
            }

            if (label) {
                label.textContent = mediaId
                    ? 'Using existing media'
                    : url
                        ? 'Current image'
                        : input.dataset.chooseLabel ||
                          'Choose image file...';
            }

            if (sourceLabel) {
                sourceLabel.textContent = sourceName
                    ? (mediaId
                        ? 'Existing media: '
                        : 'Current image: ') + sourceName
                    : '';
            }

            if (url) {
                preview.src = url;
                container.classList.remove('d-none');
            } else {
                preview.removeAttribute('src');
                container.classList.add('d-none');
            }
        }

        window.mediaPicker.setPreview = function (
            inputId,
            url,
            sourceName
        ) {
            setPickerPreview(inputId, url, '', sourceName || '');
        };

        window.mediaPicker.setExisting = function (
            inputId,
            media
        ) {
            setPickerPreview(
                inputId,
                media?.url || media?.thumb_url || null,
                media?.id || '',
                media?.name || media?.file_name || ''
            );
        };

        window.mediaPicker.reset = function (scope) {
            $(scope || document)
                .find('[data-media-picker]')
                .each(function () {
                    const picker = this;
                    const input = picker.querySelector(
                        '[data-media-input]'
                    );
                    const container = picker.querySelector(
                        '[data-media-preview-container]'
                    );
                    const preview = picker.querySelector(
                        '[data-media-preview]'
                    );
                    const label = picker.querySelector(
                        '[data-media-label]'
                    );
                    const removeField = picker.querySelector(
                        '[data-media-remove-field]'
                    );
                    const mediaIdField = picker.querySelector(
                        '[data-media-id-field]'
                    );
                    const sourceLabel = picker.querySelector(
                        '[data-media-source-label]'
                    );

                    if (input) {
                        input.value = '';
                    }

                    if (mediaIdField) {
                        mediaIdField.value = '';
                    }

                    if (removeField) {
                        removeField.value = '0';
                    }

                    if (preview) {
                        preview.removeAttribute('src');
                    }

                    if (container) {
                        container.classList.add('d-none');
                    }

                    if (sourceLabel) {
                        sourceLabel.textContent = '';
                    }

                    if (label) {
                        label.textContent = input?.dataset.chooseLabel ||
                            'Choose image file...';
                    }
                });
        };

        function escapeHtml(value) {
            return $('<div>').text(value || '').html();
        }

        function showError(message) {
            if (
                window.Swal &&
                typeof window.Swal.fire === 'function'
            ) {
                window.Swal.fire({
                    title: 'Error',
                    text: message,
                    icon: 'error',
                    type: 'error'
                });

                return;
            }

            window.alert(message);
        }

        function updatePickerToolbar() {
            const $grid = $('#mediaPickerGrid');
            const $checkboxes = $grid.find(
                '.media-picker-checkbox'
            );
            const resultCount = $checkboxes.length;
            const selectedCount = pickerState.selectedIds.size;

            $('#mediaPickerResultCount').text(resultCount);
            $('#mediaPickerBulkCount').text(selectedCount);

            $('#mediaPickerBulkDelete').prop(
                'disabled',
                selectedCount === 0
            );

            $('#mediaPickerUseSelected').prop(
                'disabled',
                selectedCount !== 1
            );

            $checkboxes.each(function () {
                const id = String($(this).data('media-id'));
                const selected = pickerState.selectedIds.has(id);

                $(this).prop('checked', selected);
                $(this)
                    .closest('.media-picker-card')
                    .toggleClass('is-active', selected);
            });

            $('#mediaPickerSelectAll').prop(
                'checked',
                resultCount > 0 &&
                $checkboxes.filter(':checked').length === resultCount
            );
        }

        function renderPickerItems(items) {
            const $grid = $('#mediaPickerGrid');

            pickerState.items.clear();
            pickerState.selectedIds.clear();
            $grid.empty();

            items.forEach(function (media) {
                const mediaId = String(media.id);
                const encodedMedia = encodeURIComponent(
                    JSON.stringify(media)
                );

                pickerState.items.set(mediaId, media);

                $grid.append(`
                    <div class="col-6 col-md-3 mb-3">
                        <div
                            class="card media-picker-card h-100 border bg-white"
                            data-media-card
                        >
                            <div class="position-relative">
                                <input
                                    type="checkbox"
                                    class="media-picker-checkbox"
                                    data-media-id="${mediaId}"
                                    value="${mediaId}"
                                    aria-label="Select ${escapeHtml(media.file_name)}"
                                >
                                <button
                                    type="button"
                                    class="btn btn-link p-0 border-0 w-100"
                                    data-picker-media="${encodedMedia}"
                                    aria-label="Use ${escapeHtml(media.name)}"
                                >
                                    <img
                                        src="${escapeHtml(media.thumb_url || media.url)}"
                                        class="card-img-top"
                                        style="height:130px;object-fit:cover;"
                                        alt="${escapeHtml(media.name)}"
                                    >
                                </button>
                            </div>

                            <div class="card-body p-2">
                                <div
                                    class="font-weight-bold small text-truncate"
                                    title="${escapeHtml(media.name)}"
                                >
                                    ${escapeHtml(media.name)}
                                </div>
                                <small class="text-muted d-block text-truncate">
                                    ${escapeHtml(media.collection_name || 'Uncategorized')}
                                </small>
                                <small class="text-muted d-block text-truncate">
                                    ${escapeHtml(media.file_name)}
                                </small>
                            </div>
                        </div>
                    </div>
                `);
            });

            updatePickerToolbar();
        }

        function loadPickerItems() {
            if (!pickerState.url) {
                return;
            }

            $('#mediaPickerLoading').removeClass('d-none');
            $('#mediaPickerGrid, #mediaPickerEmpty').addClass('d-none');

            $.getJSON(pickerState.url, {
                search: pickerState.search,
                collection: pickerState.collection,
                disk: pickerState.disk,
            })
                .done(function (response) {
                    const items = response.data || [];

                    renderPickerItems(items);
                    $('#mediaPickerEmpty').toggleClass(
                        'd-none',
                        items.length !== 0
                    );
                    $('#mediaPickerGrid').removeClass('d-none');
                })
                .fail(function (xhr) {
                    showError(
                        xhr.responseJSON?.message ||
                        'Unable to load media library.'
                    );
                })
                .always(function () {
                    $('#mediaPickerLoading').addClass('d-none');
                });
        }

        function submitPickerDelete() {
            const ids = Array.from(pickerState.selectedIds);
            const form = document.getElementById(
                'mediaPickerBulkForm'
            );

            if (!ids.length || !form) {
                return;
            }

            form.querySelectorAll('input[name="media_ids[]"]')
                .forEach(function (input) {
                    input.remove();
                });

            ids.forEach(function (id) {
                const input = document.createElement('input');

                input.type = 'hidden';
                input.name = 'media_ids[]';
                input.value = id;

                form.appendChild(input);
            });

            form.submit();
        }

        $(document).on('change', '[data-media-input]', function () {
            const input = this;
            const picker = input.closest('[data-media-picker]');
            const file = input.files && input.files[0];

            if (!picker || !file) {
                return;
            }

            const label = picker.querySelector('[data-media-label]');
            const preview = picker.querySelector('[data-media-preview]');
            const container = picker.querySelector(
                '[data-media-preview-container]'
            );
            const removeField = picker.querySelector(
                '[data-media-remove-field]'
            );
            const mediaIdField = picker.querySelector(
                '[data-media-id-field]'
            );
            const sourceLabel = picker.querySelector(
                '[data-media-source-label]'
            );

            if (label) {
                label.textContent = file.name;
            }

            if (removeField) {
                removeField.value = '0';
            }

            if (mediaIdField) {
                mediaIdField.value = '';
            }

            if (sourceLabel) {
                sourceLabel.textContent = 'New image selected';
            }

            if (preview) {
                preview.src = URL.createObjectURL(file);
            }

            if (container) {
                container.classList.remove('d-none');
            }
        });

        $(document).on('click', '[data-media-remove]', function () {
            const picker = this.closest('[data-media-picker]');

            if (!picker) {
                return;
            }

            const input = picker.querySelector('[data-media-input]');
            const label = picker.querySelector('[data-media-label]');
            const container = picker.querySelector(
                '[data-media-preview-container]'
            );
            const preview = picker.querySelector('[data-media-preview]');
            const removeField = picker.querySelector(
                '[data-media-remove-field]'
            );
            const mediaIdField = picker.querySelector(
                '[data-media-id-field]'
            );
            const sourceLabel = picker.querySelector(
                '[data-media-source-label]'
            );

            if (input) {
                input.value = '';
            }

            if (label) {
                label.textContent = input?.dataset.chooseLabel ||
                    'Choose image file...';
            }

            if (preview) {
                preview.removeAttribute('src');
            }

            if (container) {
                container.classList.add('d-none');
            }

            if (removeField) {
                removeField.value = '1';
            }

            if (mediaIdField) {
                mediaIdField.value = '';
            }

            if (sourceLabel) {
                sourceLabel.textContent = '';
            }
        });

        $(document).on('click', '[data-media-browse]', function () {
            pickerState.target = this.dataset.mediaTarget;
            pickerState.url = this.dataset.pickerUrl;
            pickerState.search = '';
            pickerState.collection = '';
            pickerState.disk = '';
            pickerState.selectedIds.clear();

            $('#mediaPickerSearch').val('');
            $('#mediaPickerCollection').val('');
            $('#mediaPickerDisk').val('');

            if (!$('#mediaPickerModal').length) {
                showError('Media picker modal is not available on this page.');
                return;
            }

            $('#mediaPickerModal').modal('show');
            loadPickerItems();
        });

        $(document).on(
            'change',
            '#mediaPickerCollection, #mediaPickerDisk',
            function () {
                pickerState.collection = $('#mediaPickerCollection').val() || '';
                pickerState.disk = $('#mediaPickerDisk').val() || '';
                loadPickerItems();
            }
        );

        $(document).on('click', '#mediaPickerRefresh', function () {
            loadPickerItems();
        });

        $(document).on('click', '#mediaPickerClearFilters', function () {
            pickerState.search = '';
            pickerState.collection = '';
            pickerState.disk = '';

            $('#mediaPickerSearch').val('');
            $('#mediaPickerCollection').val('');
            $('#mediaPickerDisk').val('');

            loadPickerItems();
        });

        $(document).on('input', '#mediaPickerSearch', function () {
            pickerState.search = this.value;

            clearTimeout(window.mediaPickerSearchTimer);

            window.mediaPickerSearchTimer = setTimeout(
                loadPickerItems,
                350
            );
        });

        $(document).on(
            'change',
            '#mediaPickerGrid .media-picker-checkbox',
            function () {
                const id = String($(this).data('media-id'));

                if ($(this).is(':checked')) {
                    pickerState.selectedIds.add(id);
                } else {
                    pickerState.selectedIds.delete(id);
                }

                updatePickerToolbar();
            }
        );

        $(document).on('click', '#mediaPickerSelectAll', function () {
            const $checkboxes = $('#mediaPickerGrid .media-picker-checkbox');
            const shouldSelectAll = $checkboxes.length > 0 &&
                $checkboxes.filter(':checked').length !== $checkboxes.length;

            $checkboxes.each(function () {
                const id = String($(this).data('media-id'));

                $(this).prop('checked', shouldSelectAll);

                if (shouldSelectAll) {
                    pickerState.selectedIds.add(id);
                } else {
                    pickerState.selectedIds.delete(id);
                }
            });

            updatePickerToolbar();
        });

        $(document).on('click', '#mediaPickerClearSelection', function () {
            pickerState.selectedIds.clear();
            updatePickerToolbar();
        });

        $(document).on('click', '#mediaPickerBulkDelete', function () {
            if (!pickerState.selectedIds.size) {
                return;
            }

            const proceed = function () {
                submitPickerDelete();
            };

            if (
                window.Swal &&
                typeof window.Swal.fire === 'function'
            ) {
                window.Swal.fire({
                    title: 'Move selected media to trash?',
                    text: pickerState.selectedIds.size +
                        ' image(s) will be moved to Trash.',
                    icon: 'warning',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, move to trash',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (result.isConfirmed || result.value) {
                        proceed();
                    }
                });
            } else if (window.confirm(
                'Move selected media to trash?'
            )) {
                proceed();
            }
        });

        $(document).on('click', '[data-picker-media]', function (event) {
            event.preventDefault();

            const $card = $(this).closest('.media-picker-card');
            const $checkbox = $card.find('.media-picker-checkbox').first();

            if (!$checkbox.length) {
                return;
            }

            $checkbox
                .prop('checked', !$checkbox.is(':checked'))
                .trigger('change');
        });

        $(document).on('click', '#mediaPickerUseSelected', function () {
            if (pickerState.selectedIds.size !== 1) {
                return;
            }

            const id = Array.from(pickerState.selectedIds)[0];
            const media = pickerState.items.get(id);

            if (media && pickerState.target) {
                window.mediaPicker.setExisting(
                    pickerState.target,
                    media
                );
            }

            $('#mediaPickerModal').modal('hide');
        });

        $('#mediaPickerModal').on('hidden.bs.modal', function () {
            pickerState.selectedIds.clear();
            updatePickerToolbar();
        });
    })(jQuery);
</script>
