<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class SecurityController extends Controller
{
    /**
     * Show the user's security settings page.
     */
    public function edit(TwoFactorAuthenticationRequest $request): Response
    {
        $props = [
            // Passkey dinonaktifkan sementara di config/fortify.php, tapi prop
            // ini tetap dikirim agar kontraknya tidak berubah saat dihidupkan.
            'canManagePasskeys' => Features::canManagePasskeys(),
            'passkeys' => Features::canManagePasskeys()
                ? $request->user()
                    ->passkeys()
                    ->select(['id', 'name', 'credential', 'created_at', 'last_used_at'])
                    ->latest()
                    ->get()
                    ->map(fn ($passkey) => [
                        'id' => $passkey->id,
                        'name' => $passkey->name,
                        'authenticator' => $passkey->authenticator,
                        'created_at_diff' => $passkey->created_at->diffForHumans(),
                        'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
                    ])
                    ->values()
                    ->all()
                : [],
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            ...$this->twoFactorProps($request),
        ];

        return Inertia::render('admin/settings/security', $props);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Password updated.')]);

        return back();
    }

    /**
     * QR code, kunci rahasia, dan kode pemulihan hanya dikirim saat 2FA sedang
     * disiapkan atau sudah dikonfirmasi, tidak pernah sebelum itu.
     *
     * @return array<string, mixed>
     */
    private function twoFactorProps(TwoFactorAuthenticationRequest $request): array
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            return ['canManageTwoFactor' => false];
        }

        $user = $request->user();
        $enabled = $user->two_factor_secret !== null;
        $confirmed = $enabled && $user->two_factor_confirmed_at !== null;

        return [
            'canManageTwoFactor' => true,
            'twoFactorEnabled' => $enabled,
            'twoFactorConfirmed' => $confirmed,
            'twoFactorQrCodeSvg' => $enabled && ! $confirmed ? $user->twoFactorQrCodeSvg() : null,
            'twoFactorSecretKey' => $enabled && ! $confirmed ? decrypt($user->two_factor_secret) : null,
            'twoFactorRecoveryCodes' => $confirmed ? $user->recoveryCodes() : [],
        ];
    }
}
