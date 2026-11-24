<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreNovelVolumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Book $book */
        $book = $this->route('book');

        return $this->user()?->can('update', $book) ?? false;
    }

    public function rules(): array
    {
        return [
            'volume_number' => [
                'required',
                'integer',
                'min:1',
                'max:65535',
                Rule::unique('novel_volumes', 'volume_number')->where('book_id', $this->route('book')->id),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'digital_license_count' => ['required', 'integer', 'min:0', 'max:1000000'],
            'digital_lending_allowed' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
            'digital_file' => ['nullable', 'file', 'mimes:pdf,epub', 'extensions:pdf,epub', 'max:51200'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->boolean('digital_lending_allowed') && ! $this->hasFile('digital_file')) {
                    $validator->errors()->add('digital_file', 'Unggah file PDF atau EPUB sebelum mengizinkan peminjaman digital.');
                }
            },
        ];
    }
}
