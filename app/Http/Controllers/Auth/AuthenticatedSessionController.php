<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Logging;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->validateCredentials();

        if (Features::enabled(
            Features::twoFactorAuthentication()
        ) && $user->hasEnabledTwoFactorAuthentication()) {
            $this->logAction(Logging::ACTION_MFA_ENABLED, $user, $request);
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $request->boolean('remember'),
            ]);
            return to_route('two-factor.login');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $this->logAction(Logging::ACTION_LOGIN, $user, $request);
        $this->logAction(Logging::ACTION_LOGIN_SUCCESS, $user, $request);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        Auth::guard('web')->logout();

        Logging::log(
            Logging::ACTION_LOGOUT,
            [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ],
            $user->id
        );
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Log user actions
     *
     * @param int $action The action to log (use constants from Logging model)
     * @param mixed $user The user performing the action
     * @param Request $request The current request
     */
    private function logAction(int $action, $user, Request $request): void
    {
        Logging::log(
            $action,
            [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ],
            $user->id
        );
    }
}
