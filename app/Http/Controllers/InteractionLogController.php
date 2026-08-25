<?php

namespace App\Http\Controllers;

use App\Http\Requests\InteractionLogs\StoreInteractionLogRequest;
use App\Http\Requests\InteractionLogs\UpdateInteractionLogRequest;
use App\Models\InteractionLog;
use App\Services\InteractionLogs\ManagementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InteractionLogController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ManagementService $managementService,
    ) {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInteractionLogRequest $request): JsonResponse|RedirectResponse
    {
        $interactionLog = $this->managementService->store($request);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Interaction logged successfully.',
                'interaction_log' => $interactionLog,
            ], 201);
        }

        return redirect()->back()->with('success', 'Interaction logged successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInteractionLogRequest $request, InteractionLog $interactionLog): JsonResponse|RedirectResponse
    {
        $interactionLog = $this->managementService->update($request, $interactionLog);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Interaction log updated successfully.',
                'interaction_log' => $interactionLog,
            ]);
        }

        return redirect()->back()->with('success', 'Interaction log updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, InteractionLog $interactionLog): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $interactionLog);

        $this->managementService->delete($interactionLog, $request->user());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Interaction log deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Interaction log deleted successfully.');
    }

    /**
     * Permanently remove the specified resource from storage.
     */
    public function forceDelete(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $interactionLog = InteractionLog::withTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $interactionLog);

        $this->managementService->forceDelete($interactionLog, $request->user());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Interaction log permanently deleted.']);
        }

        return redirect()->back()->with('success', 'Interaction log permanently deleted.');
    }
}
