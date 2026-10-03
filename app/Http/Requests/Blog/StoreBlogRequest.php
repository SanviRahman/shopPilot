<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('admin')?->can('blogs.create');
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('slug')) $this->merge(['slug' => Str::slug((string) $this->input('slug'))]);
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255', 'unique:blogs,slug'], 'description' => ['required', 'string', 'max:1000'], 'content' => ['required', 'string']];
    }
}
