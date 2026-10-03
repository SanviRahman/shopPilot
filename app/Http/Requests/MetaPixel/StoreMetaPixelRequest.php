<?php

namespace App\Http\Requests\MetaPixel;

use App\Models\MetaPixel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMetaPixelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->can('meta-pixels.create');
    }

    protected function prepareForValidation(): void
    {
        $entries = collect($this->input('pixel_entries', []))
            ->filter(fn ($entry) => is_array($entry))
            ->map(fn (array $entry) => [
                'pixel_id' => trim((string) ($entry['pixel_id'] ?? '')),
                'script' => trim((string) ($entry['script'] ?? '')),
            ])
            ->filter(fn (array $entry) => $entry['pixel_id'] !== '' || $entry['script'] !== '')
            ->values()
            ->all();

        // Backward-compatible normalization for any old form/request payload.
        if ($entries === [] && $this->filled('pixel_ids_text')) {
            $legacyIds = preg_split('/[\s,]+/', trim((string) $this->input('pixel_ids_text')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $legacyScript = trim((string) $this->input('full_script'));

            $entries = collect($legacyIds)
                ->unique()
                ->values()
                ->map(fn ($id, $index) => [
                    'pixel_id' => (string) $id,
                    'script' => $index === 0 ? $legacyScript : '',
                ])
                ->all();
        }

        $pixelIds = collect($entries)
            ->pluck('pixel_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $firstScript = collect($entries)
            ->pluck('script')
            ->first(fn ($script) => filled($script));

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'pixel_entries' => $entries,
            'pixel_ids' => $pixelIds,
            // Keep the legacy column synchronized with the first script only.
            'full_script' => $firstScript ?: null,
            'track_page_view' => $this->boolean('track_page_view'),
            'track_ecommerce' => $this->boolean('track_ecommerce'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'pixel_entries' => ['required', 'array', 'min:1', 'max:20'],
            'pixel_entries.*.pixel_id' => ['required', 'string', 'regex:/^[0-9]{5,30}$/', 'distinct'],
            'pixel_entries.*.script' => ['nullable', 'string', 'max:200000'],
            'pixel_ids' => ['required', 'array', 'min:1', 'max:20'],
            'pixel_ids.*' => ['required', 'string', 'regex:/^[0-9]{5,30}$/', 'distinct'],
            'full_script' => ['nullable', 'string', 'max:200000'],
            'lifecycle_status' => ['required', Rule::in(MetaPixel::LIFECYCLE)],
            'track_page_view' => ['boolean'],
            'track_ecommerce' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'pixel_entries.required' => 'Add at least one Pixel ID.',
            'pixel_entries.*.pixel_id.required' => 'Pixel ID is required.',
            'pixel_entries.*.pixel_id.regex' => 'Pixel ID must contain only 5 to 30 digits.',
            'pixel_entries.*.pixel_id.distinct' => 'Duplicate Pixel IDs are not allowed.',
        ];
    }
}
