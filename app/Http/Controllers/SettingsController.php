<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Tampilkan halaman pengaturan profil pengguna.
     */
    public function profile(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('settings.profile', [
            'user' => $user,
        ]);
    }

    /**
     * Tampilkan halaman pengaturan keamanan & kata sandi.
     */
    public function security(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('settings.security', [
            'user' => $user,
        ]);
    }

    /**
     * Tampilkan halaman pengaturan tampilan antarmuka & tema.
     */
    public function appearance(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('settings.appearance', [
            'user' => $user,
        ]);
    }
}
