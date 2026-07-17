<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\UpdateOrderRequest;
use App\Models\Order;
use App\Services\Orders\ManagementService;
use App\Services\Orders\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
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
     * Passes paginated orders to the Orders/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only(['search', 'sort_by', 'sort_direction', 'trashed', 'per_page'])
        );

        return Inertia::render('Orders/Index', $data);
    }

    /**
     * Show the form for creating a new order.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Order::class);

        return Inertia::render('Orders/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreOrderRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreOrderRequest $request): JsonResponse|RedirectResponse
    {
        $order = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($order, 201);
        }

        return redirect()->route('orders.show', $order->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single order to the Orders/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        Order $order,
        Request $request
    ): Response {
        $this->authorize('view', $order);

        $data = $this->query->getById(
            $request->user(),
            $order->id
        );

        return Inertia::render('Orders/Show', $data);
    }

    /**
     * Show the form for editing an existing order.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Order $order,
        Request $request
    ): Response {
        $this->authorize('update', $order);

        $data = $this->query->getById(
            $request->user(),
            $order->id
        );

        return Inertia::render('Orders/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateOrderRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateOrderRequest $request,
        Order $order
    ): JsonResponse|RedirectResponse {
        $order = $this->management->update($request, $order);

        if ($request->wantsJson()) {
            return response()->json($order);
        }

        return redirect()->route('orders.show', $order->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * order instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Order $order
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $order);

        $this->management->destroy($order, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('orders.index');
    }

    /**
     * Restore a soft-deleted order.
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
        $order = Order::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $order);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('orders.index');
    }

    /**
     * Permanently delete a soft-deleted order.
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
        $order = Order::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $order);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('orders.index');
    }

    /**
     * Bulk soft-delete multiple orders.
     *
     * Authorises each order individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Order $order) => $this->authorize('delete', $order)
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('orders.index');
    }

    /**
     * Bulk restore multiple soft-deleted orders.
     *
     * Authorises each order individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Order $order) => $this->authorize('restore', $order)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('orders.index');
    }

    /**
     * Get the list of selectable "owner" options for a given orderable type.
     */
    public function orderableOptions(Request $request): JsonResponse
    {
        $type = $request->query('type', '');

        $options = $this->query->getOrderableOptions($type);

        return response()->json($options);
    }
}
