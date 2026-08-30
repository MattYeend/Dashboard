<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Services\Permissions\ManagementService;
use App\Services\Permissions\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PermissionMatrixController extends Controller
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
     * Display the permissions × roles assignment matrix.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Permission::class);

        return Inertia::render('Permissions/Matrix', $this->query->getMatrixData());
    }

    /**
     * Update the assignment matrix in a single batch.
     *
     * Each row is individually authorised via the 'assign' policy before
     * any writes take place.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'assignments' => ['required', 'array'],
            'assignments.*.permission_id' => ['required', 'integer', 'exists:permissions,id'],
            'assignments.*.role_ids' => ['present', 'array'],
            'assignments.*.role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        foreach ($validated['assignments'] as $assignment) {
            $permission = Permission::findOrFail($assignment['permission_id']);
            $this->authorize('assign', $permission);
        }

        $this->management->syncMatrix($validated['assignments'], $request->user());

        return response()->json(['status' => 'ok']);
    }
}
