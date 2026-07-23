<?php

namespace App\Http\Controllers\Api;

use App\Enums\TokenAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Orders\CreatorService;
use App\Services\Orders\DeleterService;
use App\Services\Orders\QueryService;
use App\Services\Orders\UpdaterService;
use App\Traits\AuthorisesTokenAbility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use AuthorisesTokenAbility;

    public function __construct(
        private readonly QueryService $queryService,
        private readonly CreatorService $creatorService,
        private readonly UpdaterService $updaterService,
        private readonly DeleterService $deleterService,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Returns paginated orders, already formatted by the QueryService,
     * as a raw JSON response (not a resource collection, since getPaginated
     * returns a pre-shaped array rather than an Eloquent collection).
     *
     * Authorises via the 'viewAny' policy, then confirms the request's token carries the
     * 'orders:read' ability before returning data.
     */
    public function index(
        Request $request
    ): JsonResponse {
        $this->authorize('viewAny', Order::class);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::OrdersRead->value
        );

        $data = $this->queryService->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return response()->json($data);
    }

    /**
     * Store a newly created resource.
     *
     * Creates an order from validated request data via the CreatorService,
     * passing the authenticated user's ID as the acting creator.
     *
     * Authorises via the 'create' policy, then confirms the request's token carries the
     * 'orders:write' ability before persisting.
     */
    public function store(
        StoreOrderRequest $request
    ): OrderResource {
        $this->authorize('create', Order::class);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::OrdersWrite->value
        );

        $order = $this->creatorService->create(
            $request->validated(),
            $request->user()->id,
        );

        return new OrderResource($order);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single order via the QueryService, which includes formatted
     * relations and permissions metadata, as a raw JSON response.
     *
     * Authorises via the 'view' policy, then confirms the request's token carries the
     * 'orders:read' ability before returning data.
     */
    public function show(
        Request $request,
        Order $order
    ): JsonResponse {
        $this->authorize('view', $order);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::OrdersRead->value
        );

        return response()->json(
            $this->queryService->getById(
                $request->user(),
                $order->id
            )
        );
    }

    /**
     * Update the specified resource.
     *
     * Updates an order from validated request data via the UpdaterService,
     * passing the authenticated user's ID as the acting updater.
     *
     * Authorises via the 'update' policy, then confirms the request's token carries the
     * 'orders:write' ability before persisting.
     */
    public function update(
        UpdateOrderRequest $request,
        Order $order
    ): OrderResource {
        $this->authorize('update', $order);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::OrdersWrite->value
        );

        $updated = $this->updaterService->update(
            $order,
            $request->validated(),
            $request->user()->id,
        );

        return new OrderResource($updated);
    }

    /**
     * Remove the specified resource.
     *
     * Soft-deletes an order via the DeleterService, passing the authenticated
     * user's ID as the acting deleter, and returns an empty 204 response.
     *
     * Authorises via the 'delete' policy, then confirms the request's token carries the
     * 'orders:write' ability before deleting.
     */
    public function destroy(
        Request $request,
        Order $order
    ): JsonResponse {
        $this->authorize('delete', $order);
        $this->authoriseTokenAbility(
            $request,
            TokenAbility::OrdersWrite->value
        );

        $this->deleterService->delete(
            $order,
            $request->user()->id
        );

        return response()->json(null, 204);
    }
}
