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
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
            'canManagePasskeys' => Features::canManagePasskeys(),
            'passkeys' => $this->getFormattedPasskeys($request),
            'passwordRules' => Password::defaults()
                ->toPasswordRulesString(),
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();

            $props['twoFactorEnabled'] = $request->user()
                ->hasEnabledTwoFactorAuthentication();
            $props['requiresConfirmation'] = Features::optionEnabled(
                Features::twoFactorAuthentication(),
                'confirm'
            );
        }

        return Inertia::render('settings/Security', $props);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Password updated.'),
        ]);

        return back();
    }

    /**
     * Get formatted passkeys for the user.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getFormattedPasskeys(
        TwoFactorAuthenticationRequest $request
    ): array {
        if (! Features::canManagePasskeys()) {
            return [];
        }

        return $request->user()
            ->passkeys()
            ->select($this->getPasskeySelectColumns())
            ->latest()
            ->get()
            ->map($this->formatPasskey(...))
            ->values()
            ->all();
    }

    /**
     * Get the columns to select for passkeys.
     *
     * @return array<int, string>
     */
    private function getPasskeySelectColumns(): array
    {
        return [
            'id',
            'name',
            'credential',
            'created_at',
            'last_used_at',
        ];
    }

    /**
     * Format a single passkey for the response.
     *
     * @param  mixed  $passkey
     * @return array<string, mixed>
     */
    private function formatPasskey($passkey): array
    {
        return [
            'id' => $passkey->id,
            'name' => $passkey->name,
            'authenticator' => $passkey->authenticator,
            'created_at_diff' => $passkey->created_at
                ->diffForHumans(),
            'last_used_at_diff' => $passkey->last_used_at
                ?->diffForHumans(),
        ];
    }
}
