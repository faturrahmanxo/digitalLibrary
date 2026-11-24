<?php

namespace App\Http\Requests;

use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Member $member */
        $member = $this->route('member');

        return $this->user()?->can('update', $member) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'member_code' => strtoupper(trim((string) $this->input('member_code'))),
            'name' => trim((string) $this->input('name')),
            'email' => $this->filled('email') ? mb_strtolower(trim((string) $this->input('email'))) : null,
            'phone' => $this->filled('phone') ? trim((string) $this->input('phone')) : null,
            'address' => $this->filled('address') ? trim((string) $this->input('address')) : null,
        ]);
    }

    public function rules(): array
    {
        /** @var Member $member */
        $member = $this->route('member');

        return [
            'member_code' => ['required', 'string', 'max:50', Rule::unique('members', 'member_code')->ignore($member->id)],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'string', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_code.required' => 'Kode anggota wajib diisi.',
            'member_code.max' => 'Kode anggota maksimal 50 karakter.',
            'member_code.unique' => 'Kode anggota ini sudah digunakan oleh anggota lain.',
            'name.required' => 'Nama anggota wajib diisi.',
            'name.max' => 'Nama anggota maksimal 150 karakter.',
            'email.email' => 'Format alamat email anggota tidak valid.',
            'phone.max' => 'Nomor telepon maksimal 50 karakter.',
            'address.max' => 'Alamat maksimal 1000 karakter.',
        ];
    }
}
