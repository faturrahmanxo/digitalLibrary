<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use RuntimeException;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/\A[a-z0-9._]+\z/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
        ], [
            'name.required' => 'Nama tampilan wajib diisi.',
            'name.max' => 'Nama tampilan maksimal 100 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 30 karakter.',
            'username.regex' => 'Username hanya boleh berisi huruf kecil, angka, titik, dan garis bawah.',
            'username.unique' => 'Username tersebut sudah digunakan.',
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'];

        if (! $user->save()) {
            throw new RuntimeException('Informasi profil gagal disimpan.');
        }

        return back(fallback: route('profile.edit'))
            ->with('success', 'Informasi akun berhasil diperbarui.');
    }

    public function storePhoto(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'photo.required' => 'Pilih foto profil terlebih dahulu.',
            'photo.image' => 'File yang dipilih harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $path = $validated['photo']->store('profile-photos', 'public');

        if ($path === false) {
            throw new RuntimeException('Foto profil gagal disimpan.');
        }

        $previousPath = $user->profile_photo_path;
        $photoSaved = false;

        try {
            $user->profile_photo_path = $path;
            $photoSaved = $user->save();

            if (! $photoSaved) {
                throw new RuntimeException('Foto profil gagal disimpan.');
            }
        } finally {
            if (! $photoSaved) {
                Storage::disk('public')->delete($path);
            }
        }

        $this->deleteProfilePhotoFile($previousPath, $user->id);

        return back(fallback: route('profile.edit'))
            ->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $previousPath = $user->profile_photo_path;
        $user->profile_photo_path = null;

        if (! $user->save()) {
            throw new RuntimeException('Foto profil gagal dihapus.');
        }

        $this->deleteProfilePhotoFile($previousPath, $user->id);

        return back(fallback: route('profile.edit'))
            ->with('success', 'Foto profil berhasil dihapus.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user->password = Hash::make($validated['password']);

        if (! $user->save()) {
            throw new RuntimeException('Kata sandi gagal diperbarui.');
        }

        return back(fallback: route('profile.edit'))
            ->with('success', 'Kata sandi berhasil diperbarui.');
    }

    private function deleteProfilePhotoFile(?string $path, int $userId): void
    {
        if ($path === null || dirname($path) !== 'profile-photos') {
            return;
        }

        if (! Storage::disk('public')->delete($path)) {
            Log::warning('A profile photo could not be removed from public storage.', [
                'user_id' => $userId,
            ]);
        }
    }
}
