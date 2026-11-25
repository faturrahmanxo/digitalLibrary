<?php

namespace App\Http\Requests;

use App\Models\Book;
use App\Models\Publisher;
use App\Rules\SafeExternalUrl;
use App\Rules\ValidIsbn;
use App\Support\HtmlSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Book::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $collectionTypeInput = strtolower(trim((string) $this->input('collection_type', '')));
        if ($collectionTypeInput === '' || $collectionTypeInput === 'physical' || $collectionTypeInput === 'buku fisik') {
            $collectionType = 'physical';
        } elseif (in_array($collectionTypeInput, ['ebook', 'e-book'], true)) {
            $collectionType = 'ebook';
        } elseif ($collectionTypeInput === 'novel') {
            $collectionType = 'novel';
        } else {
            $collectionType = $collectionTypeInput;
        }

        $publisherId = $this->filled('publisher_id') ? (int) $this->input('publisher_id') : null;
        $publisherName = $this->filled('publisher') ? trim((string) $this->input('publisher')) : null;

        if ($publisherId === null && $publisherName !== null) {
            $existingPub = Publisher::where('name', $publisherName)->first();
            if ($existingPub) {
                $publisherId = $existingPub->id;
            }
        }

        $merged = [
            'title' => trim((string) $this->input('title')),
            'author' => trim((string) $this->input('author')),
            'collection_type' => $collectionType,
            'isbn' => $this->filled('isbn') ? trim((string) $this->input('isbn')) : null,
            'publisher' => $publisherName,
            'publisher_id' => $publisherId,
            'series_id' => $this->filled('series_id') ? (int) $this->input('series_id') : null,
            'series_title' => $this->filled('series_title') ? trim((string) $this->input('series_title')) : null,
            'volume_label' => $this->filled('volume_label') ? trim((string) $this->input('volume_label')) : null,
            'volume_number' => $this->filled('volume_number') ? (int) $this->input('volume_number') : null,
            'edition' => $this->filled('edition') ? trim((string) $this->input('edition')) : null,
            'status' => $this->filled('status') ? trim((string) $this->input('status')) : 'active',
        ];

        if ($this->exists('description')) {
            $merged['description'] = HtmlSanitizer::clean($this->input('description'));
        }

        if ($this->exists('synopsis')) {
            $merged['synopsis'] = HtmlSanitizer::clean($this->input('synopsis'));
        }

        if ($collectionType === 'physical') {
            $merged['stock_total'] = $this->input('stock_total') ?? $this->input('total_copies') ?? 1;
        } else {
            // Ebook: shelf must not be set
            if (! $this->has('stock_total')) {
                $merged['stock_total'] = 0;
            }
        }

        $this->merge($merged);
    }

    public function rules(): array
    {
        $collectionType = $this->input('collection_type');
        $isPhysical = $collectionType === 'physical';

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'collection_type' => ['required', 'string', 'in:physical,ebook,novel,e-book'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'isbn' => ['nullable', 'string', 'max:50', new ValidIsbn],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publisher_id' => ['nullable', 'integer', Rule::exists('publishers', 'id')],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:2100'],
            'series_id' => ['nullable', 'integer', Rule::exists('books', 'id')],
            'series_title' => ['nullable', 'string', 'max:255'],
            'volume_label' => ['nullable', 'string', 'max:100'],
            'volume_number' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'edition' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:20000'],
            'synopsis' => ['nullable', 'string', 'max:20000'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];

        if ($isPhysical) {
            $rules['stock_total'] = ['required', 'integer', 'min:0', 'max:1000000'];
            $rules['shelf_id'] = ['nullable', 'integer', Rule::exists('shelves', 'id')];
            $rules['read_url'] = ['prohibited'];
            $rules['download_url'] = ['prohibited'];
            $rules['digital_file'] = ['prohibited'];
        } else {
            $rules['stock_total'] = ['nullable', 'integer', 'min:0', 'max:1000000'];
            $rules['shelf_id'] = ['prohibited'];
            $rules['read_url'] = ['nullable', 'url:http,https', new SafeExternalUrl, 'max:2048'];
            $rules['download_url'] = ['nullable', 'url:http,https', new SafeExternalUrl, 'max:2048'];
            $rules['digital_license_count'] = ['nullable', 'integer', 'min:0', 'max:1000000'];
            $rules['digital_lending_allowed'] = ['nullable', 'boolean'];
            $rules['digital_file'] = ['nullable', 'file', 'mimes:pdf,epub', 'extensions:pdf,epub', 'max:51200'];
        }

        return $rules;
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
            'shelf_id.prohibited' => 'E-book tidak boleh ditempatkan pada rak fisik.',
            'shelf_id.exists' => 'Rak yang dipilih tidak valid.',
            'publisher_id.exists' => 'Penerbit yang dipilih tidak valid.',
            'read_url.prohibited' => 'Buku fisik tidak boleh memiliki URL baca online.',
            'download_url.prohibited' => 'Buku fisik tidak boleh memiliki URL unduhan.',
            'digital_file.prohibited' => 'Buku fisik tidak boleh mengunggah file digital.',
            'publication_year.integer' => 'Tahun terbit harus berupa angka tahun yang valid.',
            'publication_year.min' => 'Tahun terbit tidak valid.',
            'publication_year.max' => 'Tahun terbit tidak valid.',
            'cover.image' => 'Berkas sampul harus berupa gambar.',
            'cover.mimes' => 'Format sampul harus JPG, PNG, atau WEBP.',
            'cover.max' => 'Ukuran sampul maksimal 2 MB.',
        ];
    }
}
