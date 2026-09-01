<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Services\Logs\ManagementService;
use App\Services\Logs\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        private readonly QueryService $query,
        private readonly ManagementService $management,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Passes paginated activity logs to the ActivityLogs/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Log::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'action',
                'logged_in_user_id',
                'related_to_user_id',
                'date_from',
                'date_to',
                'sort_by',
                'sort_direction',
                'per_page',
            ])
        );

        return Inertia::render('ActivityLogs/Index', $data);
    }

    /**
     * Remove the specified activity log entry from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * Activity logs are hard-deleted, so this is a permanent removal
     * rather than the soft-delete used by other resources.
     */
    public function destroy(Request $request, Log $log): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $log);

        $this->management->destroy($log, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('activity-logs.index');
    }

    /**
     * Bulk permanently delete multiple activity log entries.
     *
     * Authorises each activity log individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $result = $this->management->bulkDelete(
            $request->input('ids'),
            $request->user(),
            fn (Log $log) => $this->authorize('delete', $log)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('activity-logs.index');
    }

    /**
     * Export activity logs matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Log::class);

        return $this->management->export(
            $request->only(['action', 'logged_in_user_id', 'related_to_user_id', 'date_from', 'date_to'])
        );
    }
}
