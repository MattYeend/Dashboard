<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use App\Services\Addresses\ManagementService;
use App\Services\Addresses\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
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
     * Passes paginated addresses to the Addresses/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Address::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only(['search', 'sort_by', 'sort_direction', 'trashed', 'per_page'])
        );

        return Inertia::render('Addresses/Index', $data);
    }

    /**
     * Show the form for creating a new address.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Address::class);

        $data = $this->query->getFormData();

        return Inertia::render('Addresses/Create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreAddressRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreAddressRequest $request): JsonResponse|RedirectResponse
    {
        $address = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($address, 201);
        }

        return redirect()->route('addresses.show', $address->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single address to the Addresses/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        Address $address,
        Request $request
    ): Response {
        $this->authorize('view', $address);

        $data = $this->query->getById(
            $request->user(),
            $address->id
        );

        return Inertia::render('Addresses/Show', $data);
    }

    /**
     * Show the form for editing an existing address.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Address $address,
        Request $request
    ): Response {
        $this->authorize('update', $address);

        $data = $this->query->getById(
            $request->user(),
            $address->id
        );

        return Inertia::render('Addresses/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateAddressRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateAddressRequest $request,
        Address $address
    ): JsonResponse|RedirectResponse {
        $address = $this->management->update($request, $address);

        if ($request->wantsJson()) {
            return response()->json($address);
        }

        return redirect()->route('addresses.show', $address->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * address instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Address $address
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $address);

        $this->management->destroy($address, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('addresses.index');
    }

    /**
     * Restore a soft-deleted address.
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
        $address = Address::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $address);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('addresses.index');
    }

    /**
     * Permanently delete a soft-deleted address.
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
        $address = Address::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $address);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('addresses.index');
    }

    /**
     * Bulk soft-delete multiple addresses.
     *
     * Authorises each address individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:addresses,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Address $address) => $this->authorize('delete', $address)
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('addresses.index');
    }

    /**
     * Bulk restore multiple soft-deleted addresses.
     *
     * Authorises each address individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:addresses,id'],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Address $address) => $this->authorize('restore', $address)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('addresses.index');
    }

    /**
     * Get the list of selectable "owner" options for a given addressable type.
     */
    public function addressableOptions(Request $request): JsonResponse
    {
        $type = $request->query('type', '');

        $options = $this->query->getAddressableOptions($type);

        return response()->json($options);
    }
}
