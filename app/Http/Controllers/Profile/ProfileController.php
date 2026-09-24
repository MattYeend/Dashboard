<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\DestroyProfileRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\Profile\DataPreparationService;
use App\Services\Profile\DeleterService;
use App\Services\Profile\UpdaterService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly DataPreparationService $dataPreparationService,
        private readonly UpdaterService $updaterService,
        private readonly DeleterService $deleterService,
    ) {}

    /**
     * Display the authenticated user's profile.
     */
    public function show(Request $request): Response
    {
        $this->authorize('viewOwnProfile', $request->user());

        $data = $this->dataPreparationService->forShow($request->user());

        return Inertia::render('Profile/Show', $data);
    }

    /**
     * Display the profile edit page.
     */
    public function edit(Request $request): Response
    {
        $this->authorize('editOwnProfile', $request->user());

        $data = $this->dataPreparationService->forEdit($request->user(), $request);

        return Inertia::render('Profile/Edit', $data);
    }

    /**
     * Update the authenticated user's profile. Authorisation is handled by
     * UpdateProfileRequest::authorize().
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->updaterService->update($request->user(), $request->validated());

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the authenticated user's own account. Authorisation is handled
     * by DestroyProfileRequest::authorize().
     */
    public function destroy(DestroyProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $this->deleterService->delete($user);

        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
