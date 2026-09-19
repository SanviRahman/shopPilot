@props([
    'name',
    'inputId',
    'label' => 'Image',
    'chooseLabel' => 'Choose image file...',
    'removeName' => null,
    'mediaIdName' => null,
    'pickerUrl' => null,
    'previewUrl' => null,
])

@php
    $removeName = $removeName ?: 'remove_' . $name;
    $mediaIdName = $mediaIdName ?: $name . '_media_id';
    $previewId = $inputId . '-preview';
    $previewContainerId = $inputId . '-preview-container';
@endphp

<div class="media-picker" data-media-picker>
    <label for="{{ $inputId }}" class="font-weight-600">{{ $label }}</label>

    <div class="custom-file mb-2">
        <input
            type="file"
            class="custom-file-input"
            id="{{ $inputId }}"
            name="{{ $name }}"
            accept="image/*"
            data-media-input
            data-choose-label="{{ $chooseLabel }}"
        >
        <label class="custom-file-label custom-file-label-sm" for="{{ $inputId }}" data-media-label>
            {{ $chooseLabel }}
        </label>
    </div>

    @if($pickerUrl && auth('admin')->user()?->can('media.view'))
        <button
            type="button"
            class="btn btn-outline-primary btn-sm mb-2"
            data-media-browse
            data-picker-url="{{ $pickerUrl }}"
            data-media-target="{{ $inputId }}"
        >
            <i class="fas fa-photo-video mr-1"></i> Browse Existing Media
        </button>
    @endif

    <input type="hidden" name="{{ $mediaIdName }}" value="" data-media-id-field>
    <input type="hidden" name="{{ $removeName }}" value="0" data-media-remove-field>

    <div
        id="{{ $previewContainerId }}"
        class="media-picker-preview mt-2 {{ $previewUrl ? '' : 'd-none' }}"
        data-media-preview-container
    >
        <div class="d-inline-flex align-items-start border rounded p-1 bg-light">
            <img
                id="{{ $previewId }}"
                src="{{ $previewUrl ?: '' }}"
                alt="{{ $label }} preview"
                class="img-thumbnail border-0"
                style="max-height: 100px; max-width: 180px; object-fit: cover;"
                data-media-preview
            >
            <button
                type="button"
                class="btn btn-sm btn-outline-danger ml-1"
                title="Remove image"
                data-media-remove
            >
                <i class="fas fa-times"></i>
            </button>
        </div>
        <small class="d-block text-muted mt-1" data-media-source-label></small>
    </div>
</div>
