<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\BookFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'between:1000,2100'],
            'language' => ['required', 'string', 'max:10'],
            'isbn' => ['nullable', 'string', 'max:32', Rule::unique('books', 'isbn')->ignore($this->route('book'))],
            'description' => ['nullable', 'string'],
            'format' => ['required', Rule::enum(BookFormat::class)],
            'total_copies' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'category_ids' => ['array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'cover' => ['nullable', 'image', 'max:4096'],
            'pages' => ['array'],
            'pages.*.content' => ['required', 'string'],
            'pages_provided' => ['boolean'],
        ];
    }
}
