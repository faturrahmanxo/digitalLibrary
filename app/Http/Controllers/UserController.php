<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $search = trim((string) $request->query('q', ''));
        $roleFilter = trim((string) $request->query('role', ''));
        $statusFilter = trim((string) $request->query('status', ''));

        $query = User::query();

        if ($statusFilter === 'inactive') {
            $query->onlyTrashed();
        } elseif ($statusFilter === 'all') {
            $query->withTrashed();
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search): void {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($roleFilter, [User::ROLE_ADMIN, User::ROLE_PETUGAS], true)) {
            $query->where('role', $roleFilter);
        }

        $users = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $activeAdminCount = User::where('role', User::ROLE_ADMIN)->count();

        return view('users.index', [
            'users' => $users,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
            'activeAdminCount' => $activeAdminCount,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('users.form', [
            'userModel' => new User(['role' => User::ROLE_PETUGAS]),
            'isEdit' => false,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $username = $request->validated('username');
        if (! $username) {
            $baseUsername = strtolower(Str::ascii((string) $request->validated('name')));
            $baseUsername = preg_replace('/[^a-z0-9._]+/', '_', $baseUsername) ?? '';
            $baseUsername = trim(substr($baseUsername, 0, 30), '._');
            $baseUsername = $baseUsername !== '' ? $baseUsername : 'user';
            $username = $baseUsername;
            $suffix = 1;

            while (User::withTrashed()->where('username', $username)->exists()) {
                $suffix++;
                $suffixText = '_'.$suffix;
                $username = substr($baseUsername, 0, 30 - strlen($suffixText)).$suffixText;
            }
        }

        User::create([
            'name' => $request->validated('name'),
            'username' => $username,
            'email' => $request->validated('email'),
            'role' => $request->validated('role'),
            'password' => $request->validated('password'),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('users.form', [
            'userModel' => $user,
            'isEdit' => true,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $payload = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role' => $request->validated('role'),
        ];

        if ($request->filled('username')) {
            $payload['username'] = $request->validated('username');
        }

        if ($request->filled('password')) {
            $payload['password'] = $request->validated('password');
        }

        $user->update($payload);

        return redirect()
            ->route('users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        DB::transaction(function () use ($request, $user): void {
            /** @var User $lockedUser */
            $lockedUser = User::withTrashed()->lockForUpdate()->findOrFail($user->id);

            if ($request->user()?->id === $lockedUser->id) {
                throw ValidationException::withMessages([
                    'archive' => 'Anda tidak dapat mengarsipkan akun yang sedang digunakan.',
                ]);
            }

            if ($lockedUser->isAdmin()) {
                $activeAdminCount = User::where('role', User::ROLE_ADMIN)->lockForUpdate()->count();
                if ($activeAdminCount <= 1) {
                    throw ValidationException::withMessages([
                        'archive' => 'Tidak dapat mengarsipkan Admin aktif terakhir di sistem.',
                    ]);
                }
            }

            $lockedUser->delete();
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil diarsipkan.');
    }

    public function restore(int $id): RedirectResponse
    {
        Gate::authorize('create', User::class);

        /** @var User $user */
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()
            ->route('users.index')
            ->with('success', "Akun pengguna {$user->name} berhasil diaktifkan kembali.");
    }
}
