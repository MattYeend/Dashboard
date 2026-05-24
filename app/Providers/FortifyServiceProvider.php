<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Currently Empty
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
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView($this->loginView(...));
        Fortify::resetPasswordView($this->resetPasswordView(...));
        Fortify::requestPasswordResetLinkView(
            $this->requestPasswordResetLinkView(...)
        );
        Fortify::verifyEmailView($this->verifyEmailView(...));
        Fortify::registerView($this->registerView(...));
        Fortify::twoFactorChallengeView(
            $this->twoFactorChallengeView(...)
        );
        Fortify::confirmPasswordView($this->confirmPasswordView(...));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $username = $request->input(Fortify::username());
            $throttleKey = Str::transliterate(
                Str::lower($username).'|'.$request->ip()
            );

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');
            $identifier = $credentialId !== null
                ? $credentialId
                : $request->session()->getId();

            return Limit::perMinute(10)
                ->by($identifier.'|'.$request->ip());
        });
    }

    /**
     * Render the login view.
     */
    private function loginView(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(
                Features::resetPasswords()
            ),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Render the reset password view.
     */
    private function resetPasswordView(Request $request): Response
    {
        return Inertia::render('auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
            'passwordRules' => Password::defaults()
                ->toPasswordRulesString(),
        ]);
    }

    /**
     * Render the request password reset link view.
     */
    private function requestPasswordResetLinkView(
        Request $request
    ): Response {
        return Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Render the verify email view.
     */
    private function verifyEmailView(Request $request): Response
    {
        return Inertia::render('auth/VerifyEmail', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Render the register view.
     */
    private function registerView(): Response
    {
        return Inertia::render('auth/Register', [
            'passwordRules' => Password::defaults()
                ->toPasswordRulesString(),
        ]);
    }

    /**
     * Render the two-factor challenge view.
     */
    private function twoFactorChallengeView(): Response
    {
        return Inertia::render('auth/TwoFactorChallenge');
    }

    /**
     * Render the confirm password view.
     */
    private function confirmPasswordView(): Response
    {
        return Inertia::render('auth/ConfirmPassword');
    }
}
