<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('admin')?->can('blogs.update');
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('slug')) $this->merge(['slug' => Str::slug((string) $this->input('slug'))]);
    }

    public function rules(): array
    {
        $blog = $this->route('blog');
        return ['title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($blog?->getKey())], 'description' => ['required', 'string', 'max:1000'], 'content' => ['required', 'string']];
    }
}
