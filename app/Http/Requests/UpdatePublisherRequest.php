<?php

namespace App\Http\Requests;

use App\Models\Publisher;
use App\Rules\SafeExternalUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePublisherRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Publisher $publisher */
        $publisher = $this->route('publisher');

        return $this->user()?->can('update', $publisher) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'city' => $this->filled('city') ? trim((string) $this->input('city')) : null,
            'website' => $this->filled('website') ? trim((string) $this->input('website')) : null,
            'notes' => $this->filled('notes') ? trim((string) $this->input('notes')) : null,
        ]);
    }

    public function rules(): array
    {
        /** @var Publisher $publisher */
        $publisher = $this->route('publisher');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('publishers', 'name')->ignore($publisher->id)],
            'city' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255', new SafeExternalUrl],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama penerbit wajib diisi.',
            'name.unique' => 'Nama penerbit sudah terdaftar.',
        ];
    }
}
