<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Requests\Settings\UpdateProfilePasswordRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the authenticated user's own profile.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();

        $this->authorize('viewOwnProfile', $user);

        return Inertia::render('Profile/Show', [
            'user' => $user->only(['id', 'name', 'email', 'role', 'created_at']),
            'isOwnProfile' => true,
        ]);
    }

    /**
     * Show another user's profile, read-only.
     */
    public function showOther(Request $request, User $user): Response
    {
        $this->authorize('viewOtherProfile', $user);

        return Inertia::render('Profile/Show', [
            'user' => $user->only(['id', 'name', 'email', 'role', 'created_at']),
            'isOwnProfile' => false,
        ]);
    }

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $this->authorize('editOwnProfile', $request->user());

        return Inertia::render('Profile/Edit', [
            'user' => $request->user()->only(['id', 'name', 'email']),
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->authorize('editOwnProfile', $request->user());

        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Profile updated.'),
        ]);

        return to_route('profile.edit');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(UpdateProfilePasswordRequest $request): RedirectResponse
    {
        $this->authorize('changeOwnPassword', $request->user());

        $request->user()->forceFill([
            'password' => Hash::make($request->validated('password')),
        ])->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Password updated.'),
        ]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        $this->authorize('deleteOwnProfile', $user);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
