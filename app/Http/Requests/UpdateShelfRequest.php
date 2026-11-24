<?php

namespace App\Http\Requests;

use App\Models\Shelf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShelfRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Shelf $shelf */
        $shelf = $this->route('shelf');

        return $this->user()?->can('update', $shelf) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => trim((string) $this->input('code')),
            'name' => trim((string) $this->input('name')),
            'location' => $this->filled('location') ? trim((string) $this->input('location')) : null,
            'notes' => $this->filled('notes') ? trim((string) $this->input('notes')) : null,
        ]);
    }

    public function rules(): array
    {
        /** @var Shelf $shelf */
        $shelf = $this->route('shelf');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('shelves', 'code')->ignore($shelf->id)],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode rak wajib diisi.',
            'code.unique' => 'Kode rak sudah digunakan.',
            'name.required' => 'Nama rak wajib diisi.',
        ];
    }
}
