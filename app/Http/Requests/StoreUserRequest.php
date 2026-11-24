<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ];

        if ($this->filled('username')) {
            $merge['username'] = strtolower(trim((string) $this->input('username')));
        }

        if ($this->has('role')) {
            $role = strtolower(trim((string) $this->input('role')));
            if ($role === 'staff') {
                $role = User::ROLE_PETUGAS;
            } elseif ($role === 'member') {
                $role = User::ROLE_ANGGOTA;
            }
            $merge['role'] = $role;
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['nullable', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9._]+$/', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')],
            'role' => ['required', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_PETUGAS, User::ROLE_ANGGOTA, User::ROLE_STAFF, User::ROLE_MEMBER])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 30 karakter.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, dan garis bawah.',
            'username.unique' => 'Username ini sudah digunakan.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'role.required' => 'Peran pengguna wajib dipilih.',
            'role.in' => 'Peran pengguna harus Admin, Petugas, atau Anggota.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }
}
