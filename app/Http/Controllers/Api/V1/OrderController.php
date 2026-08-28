<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    use ApiResponses;

    public function __construct(private readonly OrderService $orderService)
    {
    }

    /**
     * Creates an order. Works for a logged-in customer or a guest checkout —
     * `user_id` is nullable by design, so this route is intentionally outside
     * the `auth:sanctum` middleware group. That means `$request->user()`
     * never resolves here even when a valid Bearer token is sent — Sanctum's
     * guard only parses the token when something actually invokes it, which
     * normally happens via the `auth:sanctum` middleware. Calling the guard
     * directly resolves the token if one is present without requiring it.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createFromCheckout($request->validated(), auth('sanctum')->user());

        $order->load(['standaloneItems', 'itemGroups.items']);

        return $this->success(new OrderResource($order), 'Order placed successfully.', 201);
    }

    /** The authenticated customer's own order history. */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->success([
            'items' => OrderResource::collection($orders->items()),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order->load(['standaloneItems', 'itemGroups.items']);

        return $this->success(new OrderResource($order));
    }

    /** Re-adds every line from a past order — plain products and whole boxes alike — as a fresh cart payload for the frontend to submit through checkout again. */
    public function reorderPayload(Order $order): JsonResponse
    {
        Gate::authorize('reorder', $order);

        $order->load(['standaloneItems', 'itemGroups.items']);

        $items = $order->standaloneItems->map(fn ($item) => [
            'type' => 'product',
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
        ])->values();

        $boxItems = $order->itemGroups->map(fn ($group) => [
            'type' => 'box',
            'box_size' => $group->box_size,
            'selections' => $group->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ])->values(),
        ])->values();

        return $this->success([
            'items' => $items->concat($boxItems)->values(),
        ], 'Reorder items prepared.');
    }
}
