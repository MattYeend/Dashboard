<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStatuses\StoreOrderStatusRequest;
use App\Http\Requests\OrderStatuses\UpdateOrderStatusRequest;
use App\Models\OrderStatus;
use App\Services\OrderStatuses\ManagementService;
use App\Services\OrderStatuses\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderStatusController extends Controller
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
     * Passes paginated order statuses to the OrderStatuses/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(
        Request $request
    ): Response {
        $this->authorize(
            'viewAny',
            OrderStatus::class
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
            'OrderStatuses/Index',
            $data
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize(
            'create',
            OrderStatus::class
        );

        return Inertia::render(
            'OrderStatuses/Create'
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreOrderStatusRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StoreOrderStatusRequest $request
    ): JsonResponse|RedirectResponse {
        $orderStatus = $this->management->store(
            $request
        );

        if ($request->wantsJson()) {
            return response()->json(
                $orderStatus,
                201
            );
        }

        return redirect()->route(
            'order-statuses.index',
            $orderStatus->id
        );
    }

    /**
     * Display the specified resource.
     *
     * Passes a single orderStatus to the OrderStatuses/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        OrderStatus $orderStatus,
        Request $request
    ): Response {
        $this->authorize(
            'view',
            $orderStatus
        );

        $data = $this->query->getById(
            $request->user(),
            $orderStatus->id
        );

        return Inertia::render(
            'OrderStatuses/Show',
            $data
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        OrderStatus $orderStatus,
        Request $request
    ): Response {
        $this->authorize(
            'update',
            $orderStatus
        );

        $data = $this->query->getById(
            $request->user(),
            $orderStatus->id
        );

        return Inertia::render(
            'OrderStatuses/Edit',
            $data
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateOrderStatusRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateOrderStatusRequest $request,
        OrderStatus $orderStatus
    ): JsonResponse|RedirectResponse {
        $orderStatus = $this->management->update(
            $request,
            $orderStatus
        );

        if ($request->wantsJson()) {
            return response()->json(
                $orderStatus
            );
        }

        return redirect()->route(
            'order-statuses.index',
            $orderStatus->id
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * orderStatus instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        OrderStatus $orderStatus
    ): JsonResponse|RedirectResponse {
        $this->authorize(
            'delete',
            $orderStatus
        );

        $this->management->destroy(
            $orderStatus,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'order-statuses.index'
        );
    }

    /**
     * Restore a soft-deleted orderStatus.
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
        $orderStatus = OrderStatus::onlyTrashed()->findOrFail($id);

        $this->authorize(
            'restore',
            $orderStatus
        );

        $this->management->restore(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'order-statuses.index'
        );
    }

    /**
     * Permanently delete a soft-deleted orderStatus.
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
        $orderStatus = OrderStatus::onlyTrashed()->findOrFail($id);

        $this->authorize(
            'forceDelete',
            $orderStatus
        );

        $this->management->forceDelete(
            $id,
            $request->user()
        );

        if (request()->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'order-statuses.index'
        );
    }

    /**
     * Bulk soft-delete multiple order statuses.
     *
     * Authorises each order status individually via the 'delete' policy.
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
                'exists:order_statuses,id',
            ],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (OrderStatus $orderStatus) => $this->authorize(
                'delete',
                $orderStatus
            )
        );

        if ($request->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'order-statuses.index'
        );
    }

    /**
     * Bulk restore multiple soft-deleted order statuses.
     *
     * Authorises each order status individually via the 'restore' policy.
     */
    public function bulkRestore(
        Request $request
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            'ids' => [
                'required',
                'array',
            ],
            'ids.*' => [
                'required',
                'integer',
                'exists:order_statuses,id',
            ],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (OrderStatus $orderStatus) => $this->authorize(
                'restore',
                $orderStatus
            )
        );

        if ($request->wantsJson()) {
            return response()->json(
                null,
                204
            );
        }

        return redirect()->route(
            'order-statuses.index'
        );
    }
}
