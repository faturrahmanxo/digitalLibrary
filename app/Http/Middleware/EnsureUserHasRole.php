<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->trashed()) {
            abort(403, 'Akun Anda sedang nonaktif.');
        }

        $normalizedRoles = [];
        foreach ($roles as $role) {
            $r = strtolower(trim($role));
            $normalizedRoles[] = match ($r) {
                'staff' => User::ROLE_PETUGAS,
                'member' => User::ROLE_ANGGOTA,
                default => $r,
            };
        }

        $userRole = match ($user->role) {
            'staff' => User::ROLE_PETUGAS,
            'member' => User::ROLE_ANGGOTA,
            default => $user->role,
        };

        if (! in_array($userRole, $normalizedRoles, true)) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
