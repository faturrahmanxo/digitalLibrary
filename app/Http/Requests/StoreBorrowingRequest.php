<?php

namespace App\Http\Requests;

use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreBorrowingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Borrowing::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('request_key')) {
            $this->merge([
                'request_key' => (string) Str::uuid(),
            ]);
        }

        // If authenticated user is anggota, automatically bind to their own member record
        if ($this->user()?->isAnggota()) {
            $member = Member::firstOrCreate(
                ['email' => $this->user()->email],
                [
                    'member_code' => sprintf('AGT-%05d', Member::withTrashed()->count() + 1),
                    'name' => $this->user()->name,
                    'email' => $this->user()->email,
                    'phone' => '081200000000',
                    'address' => 'Anggota Perpustakaan',
                ]
            );

            $this->merge([
                'member_id' => $member->id,
            ]);
        }

        // Support direct book_id submission (e.g. from single-book Pinjam form)
        if ($this->filled('book_id') && ! $this->filled('items')) {
            $this->merge([
                'items' => [
                    [
                        'book_id' => (int) $this->input('book_id'),
                        'quantity' => (int) ($this->input('quantity', 1) ?: 1),
                    ],
                ],
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', Rule::exists('members', 'id')->whereNull('deleted_at')],
            'request_key' => ['required', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.book_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('books', 'id')->whereNull('deleted_at'),
            ],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'duration_days.integer' => 'Pilihan waktu peminjaman harus berupa angka hari.',
            'duration_days.min' => 'Waktu peminjaman minimal 1 hari.',
            'duration_days.max' => 'Waktu peminjaman maksimal 90 hari.',
            'member_id.required' => 'Pilih anggota peminjam.',
            'member_id.exists' => 'Anggota yang dipilih tidak valid atau sudah diarsipkan.',
            'request_key.required' => 'Kunci permintaan transaksi wajib ada.',
            'items.required' => 'Pilih minimal satu buku untuk dipinjam.',
            'items.array' => 'Format daftar buku tidak valid.',
            'items.min' => 'Pilih minimal satu buku untuk dipinjam.',
            'items.*.book_id.required' => 'Buku pada baris ini wajib dipilih.',
            'items.*.book_id.distinct' => 'Buku ini sudah dipilih. Ubah jumlah pada baris sebelumnya.',
            'items.*.book_id.exists' => 'Buku yang dipilih tidak valid atau sudah diarsipkan.',
            'items.*.quantity.required' => 'Jumlah buku wajib diisi.',
            'items.*.quantity.integer' => 'Jumlah buku harus berupa bilangan bulat.',
            'items.*.quantity.min' => 'Jumlah pinjam minimal 1 eksemplar.',
        ];
    }
}
