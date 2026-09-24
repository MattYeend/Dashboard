<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfilePasswordRequest;
use App\Services\Profile\PasswordUpdaterService;
use Illuminate\Http\RedirectResponse;

class ProfilePasswordController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly PasswordUpdaterService $passwordUpdaterService
    ) {}

    /**
     * Change the authenticated user's password. Authorisation is handled by
     * UpdateProfilePasswordRequest::authorize().
     */
    public function update(UpdateProfilePasswordRequest $request): RedirectResponse
    {
        $this->passwordUpdaterService->update(
            $request->user(),
            $request->validated('password'),
            $request->session()->getId(),
        );

        $request->session()->regenerate();

        return redirect()->route('profile.edit')->with('status', 'password-updated');
    }
}
