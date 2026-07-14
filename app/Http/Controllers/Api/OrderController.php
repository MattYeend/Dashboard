<?php

namespace App\Http\Controllers\Api;

use App\Enums\TokenAbility;
use App\Traits\AuthorisesTokenAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Orders\CreatorService;
use App\Services\Orders\DeleterService;
use App\Services\Orders\QueryService;
use App\Services\Orders\UpdaterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * Returns paginated orders as a JSON resource collection.
     *
     * Authorises via the 'viewAny' policy, then confirms the request's token carries the
     * 'orders:read' ability before returning data.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Order::class);
        $this->authoriseTokenAbility($request, TokenAbility::OrdersRead->value);

        return OrderResource::collection($this->queryService->paginate($request));
    }

    /**
     * Store a newly created resource.
     *
     * Creates an order from validated request data via the CreatorService.
     *
     * Authorises via the 'create' policy, then confirms the request's token carries the
     * 'orders:write' ability before persisting.
     */
    public function store(StoreOrderRequest $request): OrderResource
    {
        $this->authorize('create', Order::class);
        $this->authoriseTokenAbility($request, TokenAbility::OrdersWrite->value);

        return new OrderResource($this->creatorService->create($request->validated()));
    }

    /**
     * Display the specified resource.
     *
     * Returns a single order as a JSON resource.
     *
     * Authorises via the 'view' policy, then confirms the request's token carries the
     * 'orders:read' ability before returning data.
     */
    public function show(Request $request, Order $order): OrderResource
    {
        $this->authorize('view', $order);
        $this->authoriseTokenAbility($request, TokenAbility::OrdersRead->value);

        return new OrderResource($order);
    }

    /**
     * Update the specified resource.
     *
     * Updates an order from validated request data via the UpdaterService.
     *
     * Authorises via the 'update' policy, then confirms the request's token carries the
     * 'orders:write' ability before persisting.
     */
    public function update(UpdateOrderRequest $request, Order $order): OrderResource
    {
        $this->authorize('update', $order);
        $this->authoriseTokenAbility($request, TokenAbility::OrdersWrite->value);

        return new OrderResource($this->updaterService->update($order, $request->validated()));
    }

    /**
     * Remove the specified resource.
     *
     * Soft-deletes an order via the DeleterService and returns an empty 204 response.
     *
     * Authorises via the 'delete' policy, then confirms the request's token carries the
     * 'orders:write' ability before deleting.
     */
    public function destroy(Request $request, Order $order): JsonResponse
    {
        $this->authorize('delete', $order);
        $this->authoriseTokenAbility($request, TokenAbility::OrdersWrite->value);

        $this->deleterService->delete($order);

        return response()->json(null, 204);
    }
}
