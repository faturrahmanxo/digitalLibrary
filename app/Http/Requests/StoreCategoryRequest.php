<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Category::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => preg_replace('/\s+/u', ' ', trim((string) $this->input('name'))),
            'description' => $this->filled('description') ? trim((string) $this->input('description')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $normalized = mb_strtolower(trim((string) $value));
                    $exists = Category::whereRaw('LOWER(name) = ?', [$normalized])->exists();
                    if ($exists) {
                        $fail('Nama kategori sudah terdaftar.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 150 karakter.',
            'description.max' => 'Deskripsi kategori maksimal 1000 karakter.',
        ];
    }
}
