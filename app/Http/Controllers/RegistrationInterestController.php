<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationInterests\StoreRegistrationInterestRequest;
use App\Models\RegistrationInterest;
use App\Services\RegistrationInterests\ManagementService;
use App\Services\RegistrationInterests\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationInterestController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        protected readonly ManagementService $management,
        protected readonly QueryService $query,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(
        Request $request
    ): Response {
        $this->authorize(
            'viewAny',
            RegistrationInterest::class
        );

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render(
            'RegistrationInterests/Index',
            $data
        );
    }

    /**
     * Display the specified resource.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        RegistrationInterest $registrationInterest
    ): Response {
        $this->authorize(
            'view',
            $registrationInterest
        );

        $data = $this->query->getById(
            $registrationInterest->id
        );

        return Inertia::render(
            'RegistrationInterests/Show',
            $data
        );
    }

    /**
     * Store a newly submitted registration interest.
     *
     * This is a public, unauthenticated endpoint - validation is handled
     * upstream by StoreRegistrationInterestRequest, which authorises via
     * its own authorize() method (always true, since anyone may register
     * interest). No policy check is applied here, and no user account or
     * session is created.
     */
    public function store(
        StoreRegistrationInterestRequest $request
    ): RedirectResponse {
        $this->management->store(
            $request
        );

        return redirect()->route(
            'register.thanks'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        RegistrationInterest $registrationInterest
    ): JsonResponse|RedirectResponse {
        $this->authorize(
            'delete',
            $registrationInterest
        );

        $this->management->destroy(
            $registrationInterest,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'registration-interests.index'
        );
    }

    /**
     * Restore a soft-deleted registration interest.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $interest = RegistrationInterest::onlyTrashed()->findOrFail($id);

        $this->authorize(
            'restore',
            $interest
        );

        $this->management->restore(
            $id,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'registration-interests.index'
        );
    }

    /**
     * Permanently delete a soft-deleted registration interest.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $interest = RegistrationInterest::onlyTrashed()->findOrFail($id);

        $this->authorize(
            'forceDelete',
            $interest
        );

        $this->management->forceDestroy(
            $interest,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'registration-interests.index'
        );
    }

    /**
     * Bulk soft-delete multiple registration interests.
     *
     * Authorises each interest individually via the 'delete' policy.
     */
    public function bulkDelete(
        Request $request
    ): JsonResponse|RedirectResponse {
        $request->validate([
            'ids' => [
                'required',
                'array',
            ],
            'ids.*' => [
                'required',
                'integer',
                'exists:registration_interests,id',
            ],
        ]);

        $this->management->bulkDelete(
            $request->input('ids'),
            $request->user(),
            fn (RegistrationInterest $interest) => $this->authorize(
                'delete',
                $interest
            )
        );

        if ($request->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'registration-interests.index'
        );
    }

    /**
     * Bulk restore multiple soft-deleted registration interests.
     *
     * Authorises each interest individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => [
                'required',
                'array',
            ],
            'ids.*' => [
                'required', '
            integer',
                'exists:registration_interests,id',
            ],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (RegistrationInterest $interest) => $this->authorize(
                'restore',
                $interest
            )
        );

        if ($request->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'registration-interests.index'
        );
    }
}
