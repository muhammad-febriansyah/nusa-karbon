<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        // Pengguna nonaktif ditolak sebelum sesi dibuat. Pesannya sengaja tidak
        // membedakan "kredensial salah" dan "akun nonaktif" untuk kredensial
        // yang salah, agar tidak membocorkan email mana yang terdaftar.
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::query()
                ->where(Fortify::username(), $request->input(Fortify::username()))
                ->first();

            if ($user === null || ! Hash::check((string) $request->input('password'), $user->password)) {
                return null;
            }

            if (! $user->is_active) {
                throw ValidationException::withMessages([
                    Fortify::username() => 'Akun Anda dinonaktifkan. Hubungi administrator sistem.',
                ]);
            }

            return $user;
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'canUsePasskeys' => Features::canManagePasskeys(),
            'status' => $request->session()->get('status'),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/reset-password', [
            'email' => $request->email,
            'token' => $request->route('token'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/forgot-password', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/verify-email', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::registerView(fn () => Inertia::render('auth/register', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/confirm-password', [
            'canUsePasskeys' => Features::canManagePasskeys(),
        ]));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/two-factor-challenge'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)
                ->by($throttleKey)
                ->response($this->throttleResponse(...));
        });

        RateLimiter::for('passkeys', function (Request $request) {
            return Limit::perMinute(10)
                ->by(($request->input('credential.id') ?: $request->session()->getId()).'|'.$request->ip())
                ->response($this->throttleResponse(...));
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->session()->get('login.id'))
                ->response($this->throttleResponse(...));
        });
    }

    /**
     * Pesan throttle menyebutkan sisa waktu, bukan sekadar "terlalu banyak
     * percobaan". Lihat docs/09-tasks.md bagian 1.4.
     *
     * @param  array<string, mixed>  $headers
     */
    private function throttleResponse(Request $request, array $headers): RedirectResponse
    {
        $seconds = (int) ($headers['Retry-After'] ?? 60);

        $waktu = $seconds >= 60
            ? ceil($seconds / 60).' menit'
            : $seconds.' detik';

        return back()
            ->withInput($request->only(Fortify::username()))
            ->withErrors([
                Fortify::username() => "Terlalu banyak percobaan masuk. Coba lagi dalam {$waktu}.",
            ]);
    }
}
