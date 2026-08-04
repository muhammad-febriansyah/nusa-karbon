<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menendang keluar pengguna yang dinonaktifkan di tengah sesi. Pemeriksaan saat
 * login sendiri ditangani `Fortify::authenticateUsing()`.
 *
 * @see docs/09-tasks.md bagian 1.4
 */
final class EnsureUserIsActive
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->is_active) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun Anda dinonaktifkan. Hubungi administrator sistem.',
                ]);
        }

        return $next($request);
    }
}
