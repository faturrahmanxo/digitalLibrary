<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Book $book */
        $book = $this->route('book');

        return $this->user()?->can('update', $book) ?? false;
    }

    protected function prepareForValidation(): void
    {
        /** @var Book $book */
        $book = $this->route('book');

        $normalized = [
            'title' => trim((string) $this->input('title')),
            'author' => trim((string) $this->input('author')),
            'collection_type' => $this->input('collection_type', $book->collection_type),
            'isbn' => $this->filled('isbn') ? trim((string) $this->input('isbn')) : null,
            'publisher' => $this->filled('publisher') ? trim((string) $this->input('publisher')) : null,
            'download_url' => $this->filled('download_url') ? trim((string) $this->input('download_url')) : null,
        ];

        if ($this->exists('description')) {
            $normalized['description'] = $this->filled('description') ? (string) $this->input('description') : null;
        }

        if ($this->exists('synopsis')) {
            $normalized['synopsis'] = $this->filled('synopsis') ? (string) $this->input('synopsis') : null;
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'collection_type' => ['required', 'string', 'in:e-book,novel'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'stock_total' => ['required', 'integer', 'min:0', 'max:1000000'],
            'isbn' => ['nullable', 'string', 'max:50'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'download_url' => ['nullable', 'url', 'max:2048'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:2100'],
            'description' => ['nullable', 'string', 'max:20000'],
            'synopsis' => ['nullable', 'string', 'max:20000'],
            'digital_license_count' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'digital_lending_allowed' => ['nullable', 'boolean'],
            'digital_file' => ['nullable', 'file', 'mimes:pdf,epub', 'extensions:pdf,epub', 'max:51200'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul buku wajib diisi.',
            'author.required' => 'Nama penulis wajib diisi.',
            'category_id.required' => 'Kategori buku wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid atau sudah diarsipkan.',
            'stock_total.required' => 'Stok total wajib diisi.',
            'stock_total.integer' => 'Stok total harus berupa bilangan bulat.',
            'stock_total.min' => 'Stok total tidak boleh bernilai negatif.',
            'publication_year.integer' => 'Tahun terbit harus berupa angka tahun yang valid.',
            'cover.image' => 'Berkas sampul harus berupa gambar.',
            'cover.mimes' => 'Format sampul harus JPG, PNG, atau WEBP.',
            'cover.max' => 'Ukuran sampul maksimal 2 MB.',
        ];
    }
}
