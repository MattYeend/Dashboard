<?php

namespace App\Http\Controllers;

use App\Http\Requests\Activities\StoreActivityRequest;
use App\Models\Activity;
use App\Services\Activities\ManagementService;
use App\Services\Activities\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityController extends Controller
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
     * Returns a single record's paginated timeline as JSON. There is no
     * dedicated Activities/Index Inertia page — this is called from
     * ActivityTimeline.vue, embedded on the Company/Contact/Deal/Order
     * Show pages, via axios.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Activity::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'activityable_type',
                'activityable_id',
                'type',
                'search',
                'date_from',
                'date_to',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreActivityRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreActivityRequest $request): JsonResponse|RedirectResponse
    {
        $activity = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($activity, 201);
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * activity instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Activity $activity
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $activity);

        $this->management->destroy($activity, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return back();
    }

    /**
     * Restore a soft-deleted activity.
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
        $activity = Activity::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $activity);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return back();
    }

    /**
     * Permanently delete a soft-deleted activity.
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
        $activity = Activity::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $activity);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return back();
    }

    /**
     * Bulk soft-delete multiple activities.
     *
     * Authorises the bulk action itself, then each activity individually
     * via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', Activity::class);

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $result = $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Activity $activity) => $this->authorize('delete', $activity)
        );

        if (request()->wantsJson()) {
            return response()->json($result);
        }

        return back();
    }

    /**
     * Bulk restore multiple soft-deleted activities.
     *
     * Authorises the bulk action itself, then each activity individually
     * via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', Activity::class);

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $result = $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Activity $activity) => $this->authorize('restore', $activity)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back();
    }

    /**
     * Export activities matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Activity::class);

        return $this->management->export(
            $request->only([
                'activityable_type',
                'activityable_id',
                'type',
                'date_from',
                'date_to',
                'trashed',
            ])
        );
    }
}
